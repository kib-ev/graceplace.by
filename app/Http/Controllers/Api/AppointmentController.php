<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    // Добавление записи
    public function store(Request $request)
    {
        $data = $request->except(['id']);
        Log::info('API: data:' . print_r($data, true));
        // Валидация
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'place_id'  => 'required|exists:places,id',
            'start_at'  => 'required|date|after:now',
            'duration'  => 'required|integer|min:30|max:1440',
        ]);

        $start = Carbon::parse($validated['start_at']);
        Log::info($start);

        // Проверка на пересечение бронирований
        $start = Carbon::parse($validated['start_at']);
        $end = (clone $start)->addMinutes($validated['duration']);
        $overlap = Appointment::where('place_id', $validated['place_id'])
            ->whereNull('canceled_at')
            ->where(function($q) use ($start, $end) {
                $q->where(function($q2) use ($start, $end) {
                    $q2->where('start_at', '<', $end)
                        ->whereRaw('DATE_ADD(start_at, INTERVAL duration MINUTE) > ?', [$start]);
                });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false, 'error' => 'Место уже занято на это время'
            ], 409);
        } else {
            $appointment = Appointment::create($data);
            return response()->json([
                'success' => true,
                'appointment' => $appointment
            ]);
        }
    }

    // Получение свободных слотов на месте за день
    public function freeSlots(Request $request, $placeId = null)
    {
        $placeId = $placeId ?? $request->input('place_id');
        $date = $request->input('date', Carbon::now()->toDateString());
        $workStart = Carbon::parse($date.' 08:00');
        $workEnd = Carbon::parse($date.' 23:00');
        $duration = (int) $request->input('duration', 60);
        $appointments = Appointment::where('place_id', $placeId)
            ->whereNull('canceled_at')
            ->whereDate('start_at', $date)
            ->orderBy('start_at')
            ->get();
        $slots = [];
        $current = clone $workStart;
        while ($current->copy()->addMinutes($duration) <= $workEnd) {
            $slotStart = clone $current;
            $slotEnd = $current->copy()->addMinutes($duration);
            $conflict = false;
            foreach ($appointments as $a) {
                $aStart = Carbon::parse($a->start_at);
                $aEnd = (clone $aStart)->addMinutes($a->duration);
                if ($slotStart < $aEnd && $slotEnd > $aStart) {
                    $conflict = true;
                    break;
                }
            }
            if (!$conflict) {
                $slots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i')
                ];
            }
            $current->addMinutes(30); // шаг 30 минут
        }
        return response()->json(['slots' => $slots]);
    }

    // Получить список всех рабочих мест
    public function placesList()
    {
        $places = \App\Models\Place::all(['id', 'name', 'description', 'price_per_hour']);
        $result = $places->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'price_per_hour' => $p->price_per_hour . ' BYN',
                'price_per_day' => $p->price_per_hour * 8 . ' BYN',
            ];
        });
        return response()->json(['data' => $result]);
    }

    public function placesListWithSlots(Request $request)
    {
        $places = \App\Models\Place::all(['id', 'name', 'description', 'price_per_hour']);
        $result = $places->map(function($p) use ($request) {

            $json = $this->freeSlots($request, $p->id)->getContent();
            $date = $request->input('date', Carbon::now()->toDateString());
            $slotsData = json_decode($json, 1);

                return [
                'place_id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'price_per_hour' => $p->price_per_hour . ' BYN',
                'price_per_day' => $p->price_per_hour * 8 . ' BYN',
                'date' => $date,
                'free-slots' => [
                    $slotsData['slots']
                ]
            ];
        });
        return response()->json(['data' => $result]);
    }

    // Получить список всех мастеров с id и телефоном
    public function mastersList()
    {
        $masters = \App\Models\Master::with(['person'])->get();
        $result = $masters->map(function($m) {
            return [
                'user_id' => $m->user_id,
                'full_name' => trim($m->person->full_name),
                'phone' => $m->user ? $m->user->phone : null
            ];
        });
        return response()->json(['data' => $result]);
    }
}
