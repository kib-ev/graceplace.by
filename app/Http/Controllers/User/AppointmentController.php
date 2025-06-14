<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function store(Request $request)
    {
        // Валидация входящих данных
        $minDuration = (new AppointmentService())->getMinDuration();
        
        $rules = [
            'datetime' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    // Для обычных пользователей запрещаем создание записей в прошлом
                    if (!auth()->user()->hasRole('admin')) {
                        $datetime = Carbon::parse($value);
                        if ($datetime->isPast()) {
                            $fail('Выбранное время уже прошло. Пожалуйста, выберите время в будущем.');
                        }
                    }
                },
            ],
            'place_id' => 'required|exists:places,id',
            'duration' => "required|integer|min:{$minDuration}|max:960", // от минимального времени до 16 часов
            'comment' => 'nullable|string|max:1000'
        ];

        // Добавляем правило для выбора мастера, если пользователь администратор
        if (auth()->user()->hasRole('admin')) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Определяем пользователя для записи
            $userId = auth()->user()->hasRole('admin') ? $request->get('user_id') : auth()->id();

            // Проверяем доступность временного слота
            $startAt = Carbon::parse($request->get('datetime'));
            $endAt = $startAt->copy()->addMinutes($request->get('duration'));
            
            $appointmentService = new AppointmentService();
            if (!$appointmentService->isTimeSlotAvailable($request->get('place_id'), $startAt, $endAt)) {
                return back()->withErrors(['datetime' => 'Выбранное время уже занято. Пожалуйста, выберите другое время.'])->withInput();
            }

            $appointment = new Appointment();
            $appointment->fill([
                'place_id' => $request->get('place_id'),
                'duration' => $request->get('duration'),
                'start_at' => $startAt,
                'user_id' => $userId,
                'is_created_by_user' => true
            ]);

            $appointment->save();

            if($appointment->id && $request->get('comment')) {
                $appointment->addComment(Auth::user(), $request->get('comment'), Appointment::BOOKING_ADD_COMMENT);
            }

            if($appointment->id) {
                return back()->with('success', 'Запись успешно создана');
            } else {
                return back()->withErrors('Ошибка сохранения.');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create appointment: ' . $e->getMessage());
            return back()->withErrors('Произошла ошибка при создании записи. Пожалуйста, попробуйте еще раз.');
        }
    }

    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        try {
            $result = (new AppointmentService())->cancelAppointment(auth()->user(), $appointment, $request->input('cancellation_reason'));

            return response()->json([
                'success' => true,
                'message' => 'Запись успешно отменена.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
