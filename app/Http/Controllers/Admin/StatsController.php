<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\Master;
use App\Models\Appointment;
use App\Models\StorageBooking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Количество мест, мастеров
        $placesCount = Place::count();
        $mastersCount = Master::count();

        // 2. Общая статистика по записям
        $appointmentsStats = Appointment::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN appointments.canceled_at IS NULL THEN 1 ELSE 0 END) as visited,
            SUM(CASE WHEN appointments.canceled_at IS NOT NULL THEN 1 ELSE 0 END) as canceled,
            SUM(CASE WHEN appointments.is_created_by_user = 1 THEN 1 ELSE 0 END) as self_added,
            SUM(CASE WHEN appointments.canceled_at IS NULL THEN appointments.duration ELSE 0 END) as total_duration,
            COALESCE(SUM(CASE WHEN appointments.canceled_at IS NULL THEN (payment_requirements.expected_amount - payment_requirements.remaining_amount) ELSE 0 END), 0) as total_price,
            SUM(CASE WHEN appointments.canceled_at IS NULL AND appointments.duration >= 480 THEN 1 ELSE 0 END) as over_8_hours,
            SUM(CASE WHEN appointments.canceled_at IS NULL AND appointments.duration = 60 THEN 1 ELSE 0 END) as duration_1_hour
        ')
        ->leftJoin('payment_requirements', function($join) {
            $join->on('appointments.id', '=', 'payment_requirements.payable_id')
                 ->where('payment_requirements.payable_type', '=', 'App\Models\Appointment');
        })
        ->first();

        $canceledWithPaymentCount = Appointment::whereNotNull('canceled_at')
            ->whereHas('payments', function ($query) {
                $query->where('status', 'completed')
                    ->where('amount', '>', 0);
            })
            ->count();

        // Локер: выручка из Payments (completed) — фактически полученные деньги
        $totalLockerRevenue = DB::table('payments')
            ->join('storage_bookings', function ($join) {
                $join->on('storage_bookings.id', '=', 'payments.payable_id')
                    ->where('payments.payable_type', '=', StorageBooking::class);
            })
            ->where('payments.status', Payment::STATUS_COMPLETED)
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as total')
            ->value('total') ?? 0;

        // 3-11. Месячная статистика: текущий год и два предыдущих
        $currentYear = now()->year;
        $years = [$currentYear, $currentYear - 1, $currentYear - 2];

        $monthlyStats = [];
        $newMasters = [];
        $uniqueMasters = [];

        foreach ($years as $year) {
            $monthlyStats[$year] = Appointment::selectRaw('
                MONTH(appointments.start_at) as month,
                COALESCE(SUM(CASE WHEN appointments.canceled_at IS NULL THEN (payment_requirements.expected_amount - payment_requirements.remaining_amount) ELSE 0 END), 0) as revenue,
                SUM(CASE WHEN appointments.canceled_at IS NULL THEN appointments.duration ELSE 0 END) as hours
            ')
                ->leftJoin('payment_requirements', function($join) {
                    $join->on('appointments.id', '=', 'payment_requirements.payable_id')
                         ->where('payment_requirements.payable_type', '=', 'App\Models\Appointment');
                })
                ->whereYear('appointments.start_at', $year)
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $newMasters[$year] = Master::selectRaw('
                MONTH(created_at) as month,
                COUNT(*) as count
            ')
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            $uniqueMasters[$year] = Appointment::selectRaw('
                MONTH(start_at) as month,
                COUNT(DISTINCT user_id) as count
            ')
                ->whereYear('start_at', $year)
                ->whereNull('canceled_at')
                ->groupBy('month')
                ->get()
                ->keyBy('month');

            // Локер: выручка из Payments (completed), группировка по start_at бронирования
            $lockerStats[$year] = DB::table('payments')
                ->join('storage_bookings', function ($join) {
                    $join->on('storage_bookings.id', '=', 'payments.payable_id')
                        ->where('payments.payable_type', '=', StorageBooking::class);
                })
                ->where('payments.status', Payment::STATUS_COMPLETED)
                ->whereYear('storage_bookings.start_at', $year)
                ->selectRaw('MONTH(storage_bookings.start_at) as month, COALESCE(SUM(payments.amount), 0) as locker_revenue')
                ->groupBy('month')
                ->get()
                ->keyBy('month');
        }

        // 12. Недели за прошлый и текущий год (выручка и часы)
        $weeklyStats = function($year) {
            $weeks = collect();
            $firstMonday = (new \DateTime("$year-01-01"))->modify('Monday this week');
            $lastDay = new \DateTime("$year-12-31");
            $week = 1;
            $current = clone $firstMonday;
            while ($current <= $lastDay) {
                $weeks->push([
                    'yearweek' => $current->format('oW'),
                    'week_start' => $current->format('Y-m-d'),
                    'hours' => 0,
                    'revenue' => 0
                ]);
                $current->modify('+1 week');
                $week++;
            }
            $stats = Appointment::selectRaw('
                YEARWEEK(appointments.start_at, 1) as yearweek,
                MIN(DATE(appointments.start_at)) as week_start,
                SUM(CASE WHEN appointments.canceled_at IS NULL THEN appointments.duration ELSE 0 END) as hours,
                COALESCE(SUM(CASE WHEN appointments.canceled_at IS NULL THEN (payment_requirements.expected_amount - payment_requirements.remaining_amount) ELSE 0 END), 0) as revenue
            ')
                ->leftJoin('payment_requirements', function($join) {
                    $join->on('appointments.id', '=', 'payment_requirements.payable_id')
                         ->where('payment_requirements.payable_type', '=', 'App\Models\Appointment');
                })
                ->whereYear('appointments.start_at', $year)
                ->groupBy('yearweek')
                ->orderBy('yearweek')
                ->get()
                ->keyBy('yearweek');
            // Объединяем недели с данными
            $weeks = $weeks->map(function($week) use ($stats) {
                $key = $week['yearweek'];
                if ($stats->has($key)) {
                    $week['hours'] = $stats[$key]->hours;
                    $week['revenue'] = $stats[$key]->revenue;
                }
                return $week;
            });
            return $weeks;
        };
        $weeklyStatsPrev = $weeklyStats(now()->subYear()->year);
        $weeklyStatsCurr = $weeklyStats(now()->year);

        // Распределение по продолжительности (0.5ч — 12ч), только актуальные записи
        $durationBuckets = [30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330, 360, 390, 420, 450, 480, 510, 540, 570, 600, 630, 660, 690, 720];
        $durationStats = Appointment::whereNull('canceled_at')
            ->whereIn('duration', $durationBuckets)
            ->leftJoin('payment_requirements', function ($join) {
                $join->on('appointments.id', '=', 'payment_requirements.payable_id')
                    ->where('payment_requirements.payable_type', '=', 'App\Models\Appointment');
            })
            ->selectRaw('appointments.duration, COUNT(*) as count, COALESCE(SUM(payment_requirements.expected_amount - payment_requirements.remaining_amount), 0) as revenue')
            ->groupBy('appointments.duration')
            ->get()
            ->keyBy('duration');
        $durationOtherCount = Appointment::whereNull('canceled_at')
            ->whereNotIn('duration', $durationBuckets)
            ->count();
        $durationOtherRevenue = Appointment::whereNull('canceled_at')
            ->whereNotIn('duration', $durationBuckets)
            ->leftJoin('payment_requirements', function ($join) {
                $join->on('appointments.id', '=', 'payment_requirements.payable_id')
                    ->where('payment_requirements.payable_type', '=', 'App\Models\Appointment');
            })
            ->selectRaw('COALESCE(SUM(payment_requirements.expected_amount - payment_requirements.remaining_amount), 0) as revenue')
            ->value('revenue') ?? 0;
        $visited = $appointmentsStats->visited;

        return view('admin.stats', compact(
            'placesCount', 'mastersCount', 'appointmentsStats',
            'years', 'monthlyStats', 'newMasters', 'uniqueMasters', 'lockerStats',
            'weeklyStatsPrev', 'weeklyStatsCurr',
            'canceledWithPaymentCount', 'totalLockerRevenue',
            'durationBuckets', 'durationStats', 'durationOtherCount', 'durationOtherRevenue', 'visited'
        ));
    }
} 