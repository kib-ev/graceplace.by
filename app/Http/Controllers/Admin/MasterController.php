<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Master;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Place;
use App\Models\User;
use App\Models\PaymentRequirement;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $activeCount = Master::query()
            ->whereHas('user', function (Builder $user) {
                $user->where('is_active', 1);
            })
            ->count();

        $inactiveCount = Master::query()
            ->whereHas('user', function (Builder $user) {
                $user->where('is_active', 0);
            })
            ->count();

        $masters = Master::query()
            ->select('masters.*')
            ->when($request->has('is_active'), function (Builder $masters) use ($request) {
                $masters->whereHas('user', function (Builder $user) use ($request) {
                    $user->where('is_active', $request->get('is_active'));
                });
            })
            ->when($search && is_numeric($search), function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('direct', 'like', '%'. $search .'%')
                        ->orWhere('instagram', 'like', '%'. $search .'%');
                });
            })
            ->when($search && !is_numeric($search), function (Builder $query) use ($search) {
                $query->whereHas('person', function (Builder $q) use ($search) {
                    $q->where('first_name', 'like',  '%'. $search .'%')
                        ->orWhere('last_name', 'like',  '%'. $search .'%');
                });
            })
            ->when($request->has('tag'), function (Builder $query) use ($request) {
                $query->whereHas('comments', function (Builder $q) use ($request) {
                    $tag = $request->get('tag');
                    $q->where('text', 'like', "%#{$tag}%");
                });
            })
            // Eager loading only required relations
            ->with(['person', 'user.settings', 'comments.user'])
            // Aggregates: counts
            ->addSelect([
                'appointments_total_count' => Appointment::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('appointments.user_id', 'masters.user_id'),
                'appointments_visit_count' => Appointment::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('appointments.user_id', 'masters.user_id')
                    ->whereNull('canceled_at'),
                'appointments_cancel_count' => Appointment::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('appointments.user_id', 'masters.user_id')
                    ->whereNotNull('canceled_at'),
                'last_appointment_at' => Appointment::query()
                    ->selectRaw('MAX(start_at)')
                    ->whereColumn('appointments.user_id', 'masters.user_id')
                    ->whereNull('canceled_at'),
                'late_cancel_count' => Appointment::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('appointments.user_id', 'masters.user_id')
                    ->whereNotNull('canceled_at')
                    ->where(function ($q) {
                        $q->whereRaw('TIMESTAMPDIFF(HOUR, canceled_at, start_at) < 24')
                          ->orWhereColumn('canceled_at', '>=', 'start_at');
                    }),
                'debt_amount_byn' => PaymentRequirement::query()
                    ->selectRaw('COALESCE(SUM(remaining_amount), 0)')
                    ->where('payable_type', Appointment::class)
                    ->where('remaining_amount', '>', 0)
                    ->whereIn('payable_id', function ($q) {
                        $q->from('appointments')
                          ->select('id')
                          ->whereColumn('appointments.user_id', 'masters.user_id')
                          ->whereNull('canceled_at')
                          ->whereRaw('TIMESTAMPADD(MINUTE, duration, start_at) <= ?', [now()]);
                    }),
            ])
            ->orderBy('masters.created_at')
            ->get();

        return view('admin.masters.index', compact('masters', 'activeCount', 'inactiveCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.masters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $master = (new UserService())->createUserMaster(
                $request->phone,
                $request->first_name,
                $request->last_name,
                $request->patronymic,
                $request->description,
                $request->instagram,
                $request->direct);

        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Display the specified resource.
     */
    public function show(Master $master)
    {
        $master->load([
            'user.appointments' => function ($q) {
                $q->with(['paymentRequirements', 'payments', 'place']);
            },
            'person',
            'comments.user',
        ]);

        $userId = $master->user_id;

        // Aggregates
        $totalCount = \App\Models\Appointment::where('user_id', $userId)->count();
        $cancelCount = \App\Models\Appointment::where('user_id', $userId)->whereNotNull('canceled_at')->count();
        $visitCount = \App\Models\Appointment::where('user_id', $userId)->whereNull('canceled_at')->count();
        $totalMinutes = (int) \App\Models\Appointment::where('user_id', $userId)->whereNull('canceled_at')->sum('duration');

        $sumExpected = \App\Models\PaymentRequirement::where('payable_type', \App\Models\Appointment::class)
            ->whereIn('payable_id', function ($q) use ($userId) {
                $q->from('appointments')->select('id')->where('user_id', $userId)->whereNull('canceled_at');
            })->sum('expected_amount');

        $sumPaid = \App\Models\PaymentRequirement::where('payable_type', \App\Models\Appointment::class)
            ->whereIn('payable_id', function ($q) use ($userId) {
                $q->from('appointments')->select('id')->where('user_id', $userId)->whereNull('canceled_at');
            })->selectRaw('SUM(expected_amount - remaining_amount) as paid_sum')->value('paid_sum');

        // Monthly stats for 2024 and 2025
        $years = [2024, 2025];
        $durationByMonth = [];
        $expectedByMonth = [];

        foreach ($years as $yr) {
            $durationRows = \App\Models\Appointment::selectRaw('MONTH(start_at) as m, SUM(duration) as s')
                ->where('user_id', $userId)
                ->whereNull('canceled_at')
                ->whereYear('start_at', $yr)
                ->whereDate('start_at', '<=', now())
                ->groupBy('m')
                ->pluck('s', 'm');
            $durationByMonth[$yr] = array_replace(array_fill(1, 12, 0), $durationRows->toArray());

            $expectedRows = \App\Models\PaymentRequirement::selectRaw('MONTH(appointments.start_at) as m, SUM(expected_amount) as s')
                ->join('appointments', 'appointments.id', '=', 'payment_requirements.payable_id')
                ->where('payment_requirements.payable_type', \App\Models\Appointment::class)
                ->where('appointments.user_id', $userId)
                ->whereNull('appointments.canceled_at')
                ->whereYear('appointments.start_at', $yr)
                ->whereDate('appointments.start_at', '<=', now())
                ->groupBy('m')
                ->pluck('s', 'm');
            $expectedByMonth[$yr] = array_replace(array_fill(1, 12, 0), $expectedRows->toArray());
        }

        // Place breakdown for 2025
        $durationByPlaceMonth2025 = \App\Models\Appointment::selectRaw('place_id, MONTH(start_at) as m, SUM(duration) as s')
            ->where('user_id', $userId)
            ->whereNull('canceled_at')
            ->whereYear('start_at', 2025)
            ->whereDate('start_at', '<=', now())
            ->groupBy('place_id', 'm')
            ->get();

        $expectedByPlaceMonth2025 = \App\Models\PaymentRequirement::selectRaw('appointments.place_id, MONTH(appointments.start_at) as m, SUM(payment_requirements.expected_amount) as s')
            ->join('appointments', 'appointments.id', '=', 'payment_requirements.payable_id')
            ->where('payment_requirements.payable_type', \App\Models\Appointment::class)
            ->where('appointments.user_id', $userId)
            ->whereNull('appointments.canceled_at')
            ->whereYear('appointments.start_at', 2025)
            ->whereDate('appointments.start_at', '<=', now())
            ->groupBy('appointments.place_id', 'm')
            ->get();

        $placeDuration = [];
        foreach ($durationByPlaceMonth2025 as $row) {
            $placeDuration[$row->place_id][$row->m] = (int)$row->s;
        }
        $placeExpected = [];
        foreach ($expectedByPlaceMonth2025 as $row) {
            $placeExpected[$row->place_id][$row->m] = (float)$row->s;
        }

        return view('admin.masters.show', compact(
            'master',
            'totalCount',
            'cancelCount',
            'visitCount',
            'totalMinutes',
            'sumExpected',
            'sumPaid',
            'durationByMonth',
            'expectedByMonth',
            'placeDuration',
            'placeExpected'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Master $master)
    {
        return view('admin.masters.edit', compact('master'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Master $master)
    {
        $person = $master->person;
        $person->fill($request->all())->save();

        $phone = $person->phones->first();
        $phone->update([
            'number' => $request->get('phone')
        ]);

        $master->user->update([
            'phone' => $phone->number
        ]);

        $master->fill($request->all());
        $master->update();

        if ($request->has('is_active')) {
            $master->user->update([
                'is_active' => $request->get('is_active') == 1
            ]);
        }

        return back();

//        return redirect()->route('admin.masters.show', $master);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
        if ($master->user->appointments()->count() == 0) {
            $master->person->phones()->delete();
            $master->person->delete();
            $master->delete();
            $master->user->delete();
        }

        return redirect()->route('admin.masters.index');
    }
}
