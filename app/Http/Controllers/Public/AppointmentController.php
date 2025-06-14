<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Master;
use App\Models\Place;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $minDuration = (new AppointmentService())->getMinDuration();

        $request->validate([
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'duration' => "required|integer|min:{$minDuration}|max:960",
            'place_id' => 'required|exists:places,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:255',
        ]);

        $master = Master::findOrFail($request->master_id);
        $place = Place::findOrFail($request->place_id);
        
        // Combine date and time
        $startAt = Carbon::parse($request->date . ' ' . $request->time);
        $endAt = $startAt->copy()->addMinutes($request->duration);

        // Check if the time slot is available
        $appointmentService = new AppointmentService();
        if (!$appointmentService->isTimeSlotAvailable($place->id, $startAt, $endAt)) {
            return back()->withErrors(['time' => 'Выбранное время уже занято. Пожалуйста, выберите другое время.'])->withInput();
        }

        // Calculate price
        $hours = $request->duration / 60;
        $price = $hours * $place->price_per_hour;

        // Create appointment
        $appointment = new Appointment([
            'user_id' => $master->user_id,
            'place_id' => $place->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration' => $request->duration,
            'price' => $price,
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
        ]);

        $appointment->save();

        return redirect()->route('public.masters.show', $master)
            ->with('success', 'Запись успешно создана! Мы свяжемся с вами для подтверждения.');
    }

    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        // Проверяем, имеет ли пользователь разрешение 'cancel appointment'
        if (!auth()->user()->can('cancel appointment')) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав на отмену записи.'
            ], 403); // HTTP статус 403 (Forbidden)
        }

        if (now()->addHours(24)->greaterThan($appointment->start_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Для отмены записи менее чем за 24 часа до начала производится через Direct'
            ], 403); // HTTP статус 403 (Forbidden)
        }

        $cancellationReason = $request->input('cancellation_reason');

        $result = (new AppointmentService())->cancelAppointment(auth()->user(), $appointment,  $cancellationReason);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Запись успешно отменена.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось отменить запись.'
            ]);
        }

    }
}
