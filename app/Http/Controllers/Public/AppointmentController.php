<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $appointment = new Appointment();
        $appointment->fill($request->all());
        $appointment->start_at = Carbon::parse($request->get('datetime'));

        $appointment->user_id = $request->get('user_id') ?? auth()->id();
        $appointment->is_created_by_user = !auth()->user()->hasRole('admin');

        $appointment->save();

        if($appointment->id && $request->get('comment')) {
            $appointment->addComment(Auth::user(), $request->get('comment'), BOOKING_ADD_COMMENT);
        }

        if($appointment->id) {
            return back();
        } else {
            return back()->withErrors('Ошибка сохранения.');
        }
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
