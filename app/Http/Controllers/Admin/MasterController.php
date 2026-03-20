<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Master;
use App\Models\ServiceCategory;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');

        $categoryFilter = function (Builder $q) use ($categoryId) {
            $q->whereHas('serviceCategories', fn (Builder $cq) => $cq->where('service_categories.id', $categoryId));
        };

        $baseQuery = Master::query()
            ->when($categoryId, $categoryFilter);

        $counts = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'masters.user_id')
            ->selectRaw('SUM(users.is_active = 1) as active, SUM(users.is_active = 0) as inactive')
            ->first();
        $activeCount   = (int) $counts->active;
        $inactiveCount = (int) $counts->inactive;

        $debtorsBaseQuery = (clone $baseQuery)
            ->select('masters.id')
            ->join('users', 'users.id', '=', 'masters.user_id')
            ->addSelect(['debt_amount_byn' => Master::debtAmountSubquery()])
            ->havingRaw('debt_amount_byn > 0');
        $debtorsActiveCount = (clone $debtorsBaseQuery)->where('users.is_active', 1)->count();
        $debtorsInactiveCount = (clone $debtorsBaseQuery)->where('users.is_active', 0)->count();

        $masters = (clone $baseQuery)
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
                $query->where(function (Builder $q) use ($search) {
                    $q->where('first_name', 'like', '%'. $search .'%')
                        ->orWhere('last_name', 'like', '%'. $search .'%');
                });
            })
            ->when($request->has('tag'), function (Builder $query) use ($request) {
                $query->whereHas('comments', function (Builder $q) use ($request) {
                    $tag = $request->get('tag');
                    $q->where('text', 'like', "%#{$tag}%");
                });
            })
            ->when($request->get('debtors') == '1', function (Builder $query) {
                $query->havingRaw('debt_amount_byn > 0');
            })
            // Eager loading only required relations
            ->with(['user.settings', 'comments.user'])
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
                'debt_amount_byn' => Master::debtAmountSubquery(),
            ])
            ->orderBy('masters.created_at')
            ->get();

        $currentCategory = $categoryId ? ServiceCategory::find($categoryId) : null;

        return view('admin.masters.index', compact('masters', 'activeCount', 'inactiveCount', 'debtorsActiveCount', 'debtorsInactiveCount', 'currentCategory'));
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
            'serviceCategories',
            'user.appointments' => function ($q) {
                $q->with(['paymentRequirements', 'place.prices', 'user.master', 'comments.user']);
            },
            'user.storageBookings.comments.user',
            'comments.user',
        ]);

        $userId = $master->user_id;

        // 1 запрос — все счётчики и сумма минут
        $agg = \App\Models\Appointment::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(canceled_at IS NOT NULL) as cancel_count,
                SUM(canceled_at IS NULL) as visit_count,
                SUM(IF(canceled_at IS NULL, duration, 0)) as total_minutes
            ')->first();
        $totalCount   = (int) $agg->total_count;
        $cancelCount  = (int) $agg->cancel_count;
        $visitCount   = (int) $agg->visit_count;
        $totalMinutes = (int) $agg->total_minutes;

        // 1 запрос — ожидаемая сумма и оплачено
        $payAgg = \App\Models\PaymentRequirement::query()
            ->join('appointments', 'appointments.id', '=', 'payment_requirements.payable_id')
            ->where('payment_requirements.payable_type', \App\Models\Appointment::class)
            ->where('appointments.user_id', $userId)
            ->whereNull('appointments.canceled_at')
            ->selectRaw('SUM(payment_requirements.expected_amount) as sum_expected, SUM(payment_requirements.expected_amount - payment_requirements.remaining_amount) as sum_paid')
            ->first();
        $sumExpected = (float) ($payAgg->sum_expected ?? 0);
        $sumPaid     = (float) ($payAgg->sum_paid ?? 0);

        // 1 запрос — помесячная статистика по часам за все годы
        $durationRows = \App\Models\Appointment::selectRaw('YEAR(start_at) as y, MONTH(start_at) as m, SUM(duration) as s')
            ->where('user_id', $userId)
            ->whereNull('canceled_at')
            ->whereDate('start_at', '<=', now())
            ->groupBy('y', 'm')
            ->get();

        // 1 запрос — помесячная статистика по выручке за все годы
        $expectedRows = \App\Models\PaymentRequirement::selectRaw('YEAR(appointments.start_at) as y, MONTH(appointments.start_at) as m, SUM(payment_requirements.expected_amount) as s')
            ->join('appointments', 'appointments.id', '=', 'payment_requirements.payable_id')
            ->where('payment_requirements.payable_type', \App\Models\Appointment::class)
            ->where('appointments.user_id', $userId)
            ->whereNull('appointments.canceled_at')
            ->whereDate('appointments.start_at', '<=', now())
            ->groupBy('y', 'm')
            ->get();

        $years = $durationRows->pluck('y')->merge($expectedRows->pluck('y'))->unique()->sort()->values()->toArray();
        $durationByMonth = [];
        $expectedByMonth = [];
        foreach ($years as $yr) {
            $durationByMonth[$yr] = array_replace(array_fill(1, 12, 0), $durationRows->where('y', $yr)->pluck('s', 'm')->toArray());
            $expectedByMonth[$yr] = array_replace(array_fill(1, 12, 0), $expectedRows->where('y', $yr)->pluck('s', 'm')->toArray());
        }

        // 2 запроса — разбивка по местам за текущий год
        $currentYear = now()->year;
        $durationByPlaceMonth2025 = \App\Models\Appointment::selectRaw('place_id, MONTH(start_at) as m, SUM(duration) as s')
            ->where('user_id', $userId)
            ->whereNull('canceled_at')
            ->whereYear('start_at', $currentYear)
            ->whereDate('start_at', '<=', now())
            ->groupBy('place_id', 'm')
            ->get();

        $expectedByPlaceMonth2025 = \App\Models\PaymentRequirement::selectRaw('appointments.place_id, MONTH(appointments.start_at) as m, SUM(payment_requirements.expected_amount) as s')
            ->join('appointments', 'appointments.id', '=', 'payment_requirements.payable_id')
            ->where('payment_requirements.payable_type', \App\Models\Appointment::class)
            ->where('appointments.user_id', $userId)
            ->whereNull('appointments.canceled_at')
            ->whereYear('appointments.start_at', $currentYear)
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
        $serviceCategories = ServiceCategory::getTreeForSelection();
        $recommendedCategoryIds = ServiceCategory::getRecommendedIdsForText($master->description);

        return view('admin.masters.show', compact(
            'master',
            'serviceCategories',
            'recommendedCategoryIds',
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
        $master->fill($request->only(['first_name', 'last_name', 'patronymic']))->save();


        if ($request->filled('phone')) {
            $master->user->update(['phone' => $request->phone]);
        }

        $master->fill($request->only(['instagram', 'direct', 'description']))->save();

        if ($request->has('is_active')) {
            $master->user->update(['is_active' => $request->is_active == 1]);
        }

        return back();
    }

    /**
     * Update master's service categories.
     */
    public function updateServiceCategories(Request $request, Master $master)
    {
        $ids = $request->input('service_category_ids', []);
        $master->serviceCategories()->sync($ids);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
        if ($master->user->appointments()->count() == 0) {
            $master->delete();
            $master->user->delete();
        }

        return redirect()->route('admin.masters.index');
    }
}
