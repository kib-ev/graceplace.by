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
                    ->where('status', '!=', 'paid')
                    ->whereIn('payable_id', function ($q) {
                        $q->from('appointments')
                          ->select('id')
                          ->whereColumn('appointments.user_id', 'masters.user_id')
                          ->whereNull('canceled_at')
                          ->whereDate('start_at', '<=', now()->startOfDay());
                    }),
            ])
            ->orderBy('masters.created_at')
            ->get();

        return view('admin.masters.index', compact('masters'));
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
        return view('admin.masters.show', compact('master'));
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
