<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $date = now();
        if ($request->has('date')) {
            $date = \Carbon\Carbon::parse($request->get('date'));
        }

        $calendarDays = $this->getCalendarDays($date, 90);

        return view('public/index', compact('date', 'calendarDays'));
    }

    private function getCalendarDays($selectedDate, $daysCount)
    {
        $calendarDays = [];
        $startDate = now()->startOfDay();
        $endDate = now()->addDays($daysCount)->endOfDay();
        $currentDate = $startDate->clone();

        while ($currentDate <= $endDate) {
            $calendarDays[] = [
                'date' => $currentDate->format('Y-m-d'),
                'dayNumber' => $currentDate->format('d'),
                'dayOfWeek' => mb_substr($currentDate->translatedFormat('D'), 0, 2), // ПН, ВТ...
                'monthName' => $currentDate->translatedFormat('M'), // ЯНВ, ФЕВ...
                'isWeekend' => $currentDate->isWeekend(),
                'isSelected' => $currentDate->isSameDay($selectedDate),
                'url' => url('/?date=' . $currentDate->format('Y-m-d')),
            ];
            $currentDate->addDay();
        }

        return $calendarDays;
    }
}
