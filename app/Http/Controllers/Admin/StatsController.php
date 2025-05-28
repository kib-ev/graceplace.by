<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\Master;
use App\Models\Appointment;

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
            SUM(CASE WHEN canceled_at IS NULL THEN 1 ELSE 0 END) as visited,
            SUM(CASE WHEN canceled_at IS NOT NULL THEN 1 ELSE 0 END) as canceled,
            SUM(CASE WHEN is_created_by_user = 1 THEN 1 ELSE 0 END) as self_added,
            SUM(CASE WHEN canceled_at IS NULL THEN duration ELSE 0 END) as total_duration,
            SUM(CASE WHEN canceled_at IS NULL THEN price ELSE 0 END) as total_price
        ')->first();

        // 3. Месячная статистика 2024
        $monthlyStats2024 = Appointment::selectRaw('
            MONTH(start_at) as month,
            SUM(CASE WHEN canceled_at IS NULL THEN price ELSE 0 END) as revenue,
            SUM(CASE WHEN canceled_at IS NULL THEN duration ELSE 0 END) as hours
        ')
            ->whereYear('start_at', 2024)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 4. Месячная статистика 2025
        $monthlyStats2025 = Appointment::selectRaw('
            MONTH(start_at) as month,
            SUM(CASE WHEN canceled_at IS NULL THEN price ELSE 0 END) as revenue,
            SUM(CASE WHEN canceled_at IS NULL THEN duration ELSE 0 END) as hours
        ')
            ->whereYear('start_at', 2025)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 5. Новые мастера по месяцам 2024
        $newMasters2024 = Master::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as count
        ')
            ->whereYear('created_at', 2024)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 6. Новые мастера по месяцам 2025
        $newMasters2025 = Master::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as count
        ')
            ->whereYear('created_at', 2025)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 7. Уникальные мастера по месяцам (2024)
        $uniqueMasters2024 = Appointment::selectRaw('
            MONTH(start_at) as month,
            COUNT(DISTINCT user_id) as count
        ')
            ->whereYear('start_at', 2024)
            ->whereNull('canceled_at')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 8. Уникальные мастера по месяцам (2025)
        $uniqueMasters2025 = Appointment::selectRaw('
            MONTH(start_at) as month,
            COUNT(DISTINCT user_id) as count
        ')
            ->whereYear('start_at', 2025)
            ->whereNull('canceled_at')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // 9. Недели за прошлый и текущий год (выручка и часы)
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
                YEARWEEK(start_at, 1) as yearweek,
                MIN(DATE(start_at)) as week_start,
                SUM(CASE WHEN canceled_at IS NULL THEN duration ELSE 0 END) as hours,
                SUM(CASE WHEN canceled_at IS NULL THEN price ELSE 0 END) as revenue
            ')
                ->whereYear('start_at', $year)
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

        return view('admin.stats', compact(
            'placesCount', 'mastersCount', 'appointmentsStats',
            'monthlyStats2024', 'monthlyStats2025',
            'newMasters2024', 'newMasters2025',
            'uniqueMasters2024', 'uniqueMasters2025',
            'weeklyStatsPrev', 'weeklyStatsCurr'
        ));
    }
} 