<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function byHours(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (!$dateFrom && !$dateTo) {
            $single = Carbon::parse($request->input('date', now()->toDateString()))->startOfDay();
            $dateFrom = $single->toDateString();
            $dateTo = $single->toDateString();
        }

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $startHour = (int) $request->input('start_hour', 6);
        $endHour = (int) $request->input('end_hour', 24); // exclusive upper bound, allow 24 to include 23:00-00:00

        if ($startHour < 0) { $startHour = 0; }
        if ($endHour > 24) { $endHour = 24; }
        if ($endHour <= $startHour) { $endHour = $startHour + 1; }

        $places = Place::orderBy('name')->with('prices')->get();

        $appointments = Appointment::query()
            ->whereNull('canceled_at')
            ->whereBetween('start_at', [$from, $to])
            ->with('place.prices')
            ->get();

        $hours = range($startHour, $endHour - 1);

        $grid = [];
        $totalsByPlace = [];
        $totalsByHour = array_fill_keys($hours, 0.0);

        foreach ($places as $place) {
            $grid[$place->id] = array_fill_keys($hours, 0.0);
            $totalsByPlace[$place->id] = 0.0;
        }

        foreach ($appointments as $a) {
            if (!$a->place) { continue; }

            $apptStart = $a->start_at->copy();
            $apptEnd = $a->start_at->copy()->addMinutes($a->duration);

            $iterStart = $apptStart->greaterThan($from) ? $apptStart->copy() : $from->copy();
            $iterEnd = $apptEnd->lessThan($to) ? $apptEnd->copy() : $to->copy();
            if ($iterStart >= $iterEnd) { continue; }

            // Walk by hour slots across the interval
            $cursor = $iterStart->copy();
            while ($cursor < $iterEnd) {
                $slotStart = $cursor->copy()->setMinute(0)->setSecond(0);
                $slotEnd = $slotStart->copy()->addHour();

                $overlapStart = $cursor->greaterThan($slotStart) ? $cursor : $slotStart;
                $overlapEnd = $iterEnd->lessThan($slotEnd) ? $iterEnd : $slotEnd;
                $minutes = $overlapStart->diffInMinutes($overlapEnd);

                $hourOfDay = (int) $slotStart->format('G');
                if ($minutes > 0 && in_array($hourOfDay, $hours, true)) {
                    $pricePerHour = $a->place->getPriceForDate($slotStart);
                    $amount = ($pricePerHour * $minutes) / 60.0;

                    $grid[$a->place_id][$hourOfDay] += $amount;
                    $totalsByPlace[$a->place_id] += $amount;
                    $totalsByHour[$hourOfDay] += $amount;
                }

                $cursor = $slotEnd;
            }
        }

        return view('admin.revenue.hours', [
            'dateFrom' => $from,
            'dateTo' => $to,
            'startHour' => $startHour,
            'endHour' => $endHour,
            'hours' => $hours,
            'places' => $places,
            'grid' => $grid,
            'totalsByPlace' => $totalsByPlace,
            'totalsByHour' => $totalsByHour,
        ]);
    }
}


