<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Appointment;
use App\Models\Place;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Показываем страницу мастера с доступными слотами
    public function show(Master $master)
    {
        // Берём рабочее место мастера
        $place = Place::first();

        // Генерируем слоты на ближайшие 7 дней (пример)
        $slots = [];
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->addDays(7)->endOfDay();

        $appointments = Appointment::where('user_id', $master->user_id)
            ->whereBetween('start_at', [$start, $end])
            ->get();

        // Простой пример генерации слотов: каждые 1 час с 10 до 18
        for ($day = 0; $day <= 7; $day++) {
            $date = Carbon::now()->addDays($day)->toDateString();
            for ($hour = 10; $hour <= 18; $hour++) {
                $slotTime = Carbon::parse("$date $hour:00:00");

                // Проверяем занято ли время
                $isBooked = $appointments->contains(function ($a) use ($slotTime) {
                    return $slotTime->between(
                        Carbon::parse($a->start_at),
                        Carbon::parse($a->start_at)->addMinutes($a->duration)
                    );
                });

                $slots[] = [
                    'time' => $slotTime,
                    'available' => !$isBooked,
                ];
            }
        }

        return view('public.booking.show', compact('master', 'place', 'slots'));
    }

    // Обработка бронирования от клиента
    public function reserve(Request $request, Master $master)
    {
        $validated = $request->validate([
            'start_at' => 'required|date|after:now',
            'client_name' => 'required|string',
        ]);

        // Можно добавить проверку что слот ещё свободен

        Appointment::create([
            'master_id' => $master->id,
            'start_at' => $validated['start_at'],
            'duration' => 60, // Например, фиксировано 1 час
            'is_created_by_user' => true,
            'description' => 'Бронирование через сайт от ' . $validated['client_name'],
        ]);

        return redirect()->back()->with('success', 'Бронирование успешно!');
    }
}
