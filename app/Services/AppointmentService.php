<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;

final class AppointmentService
{
    public static int $defaultTimeStep = 30; // for appointment and duration
    public static int $defaultBreakTime = 30;
    public static int $minAppointmentDuration = 60; // for masters
    public static int $adminMinAppointmentDuration = 30; // for admins

    protected Collection|null $appointments = null;
    protected Carbon|null $date = null;

//    public function createAppointment(User $user, Master $master, Place $place, Carbon $startAt, int $durationMinutes, string $comment)
//    {
//        $appointment = Appointment::make();
//        $appointment->fill([
//            'user_id' => $user->id,
//            'master_id' => $master->id,
//            'place_id' => $place->id,
//            'start_at' => $startAt,
//            'duration' => $durationMinutes
//        ]);
//        $appointment->start_at = Carbon::parse($request->get('date') . ' ' . $request->get('time'));
//        $appointment->save();
//    }

    /**
     * Проверяет, нет ли пересечений текущей встречи с другими встречами в тот же день.
     *
     * @param Appointment $appointment
     * @return bool
     */
    public function hasOverlappingAppointments(Appointment $appointment): bool
    {
        $start = $appointment->start_at;
        $end = $appointment->start_at->copy()->addMinutes($appointment->duration);

        // Получаем все встречи в этот день
        $existingAppointments = Appointment::where('user_id', $appointment->user_id)
            ->whereDate('start_at', $start->toDateString())
            ->where('id', '!=', $appointment->id) // Исключаем текущую встречу, если она уже существует
            ->get();

        foreach ($existingAppointments as $existingAppointment) {
            $existingStart = $existingAppointment->start_at;
            $existingEnd = $existingAppointment->start_at->copy()->addMinutes($existingAppointment->duration);

            // Проверяем пересечение интервалов
            if ($start->lt($existingEnd) && $end->gt($existingStart)) {
                return true; // Пересечение найдено
            }
        }

        return false; // Пересечений нет
    }

    public function loadAppointments(Collection $appointments): AppointmentService
    {
        $this->appointments = $appointments->sortBy('start_at')->whereNull('canceled_at');
        return $this;
    }

    public function getBreakTime(Appointment $appointment): ?int
    {
        if(auth()->user()) {
            if($appointment->user_id == auth()->id()) {
                return 0;
            }
        }

        return self::$defaultBreakTime;
    }

    public function isTimeFree(Carbon $datetime, Collection $appointments = null): bool
    {
        $result = true;

        if(is_null($appointments)) {
            $appointments = $this->appointments;
        }

        $appointments->each(function ($appointment) use (&$result, $datetime) {
            $breakTime = $this->getBreakTime($appointment);

            if ($datetime->greaterThanOrEqualTo($appointment->start_at->subMinutes($breakTime))
                && $datetime->lessThan($appointment->start_at->addMinutes($appointment->duration + $breakTime))) {
                $result = false;
            }
        });

        return $result;
    }

    public function isTimeFreeTest(Carbon $datetime, Collection $appointments = null): bool
    {
        return !$this->isTimeAppointmentTest($datetime, $appointments);
    }

    public function isTimeAppointment(Carbon $datetime, Collection $appointments = null): bool
    {
        $result = true;

        if(is_null($appointments)) {
            $appointments = $this->appointments;
        }

        $appointments->each(function ($appointment) use (&$result, $datetime) {
            if($datetime->greaterThanOrEqualTo($appointment->start_at) && $datetime->lessThan($appointment->start_at->addMinutes($appointment->duration))) {
                $result = false;
            }
        });

        return $result;
    }

    public function isTimeAppointmentTest(Carbon $datetime, Collection $appointments = null): bool
    {
        $result = false;

        if(is_null($appointments)) {
            $appointments = $this->appointments;
        }

        $appointments->each(function ($appointment) use (&$result, $datetime) {
            if($datetime->greaterThanOrEqualTo($appointment->start_at) && $datetime->lessThan($appointment->start_at->addMinutes($appointment->duration))) {
                $result = true;
            }
        });

        return $result;
    }

    public function isTimeBreak(Carbon $datetime, Collection $appointments = null): bool
    {
        if(is_null($appointments)) {
            $appointments = $this->appointments;
        }

        return !(!$this->isTimeAppointment($datetime, $appointments) && !$this->isTimeFree($datetime, $appointments));
    }

    public function isTimeBreakTest(Carbon $checkTime, Collection $appointments = null): bool
    {
        $result = false;

        if(is_null($appointments)) {
            $appointments = $this->appointments;
        }

        if($this->isTimeFreeTest($checkTime, $appointments)) {

            $appointments->each(function ($appointment) use (&$result, $checkTime) {
                if ($checkTime->greaterThanOrEqualTo($appointment->start_at->subMinutes(self::$defaultBreakTime))
                    && $checkTime->lessThan($appointment->start_at->addMinutes($appointment->duration + self::$defaultBreakTime))) {
                    $result = true;
                }
            });

        }

        return $result;
    }

    public function getNextAppointment(Carbon $datetime)
    {
        return $this->appointments->where('start_at', '>=', $datetime)->first();
    }

    public function getMinutesToNextAppointment(Carbon $datetime): int|null
    {
        $appointment = $this->getNextAppointment($datetime);

        if($appointment) {
            $interval = CarbonInterval::minutes($datetime->diffInMinutes($appointment->start_at));

            $breakTime = $this->getBreakTime($appointment);
            if($breakTime) {
                $interval = $interval->subMinutes($breakTime);
            }
            return $interval->totalMinutes;
        }

        return null;
    }

    public function getAppointment(Carbon $datetime): Appointment|null
    {
        $isAppointment = null;

        foreach($this->appointments as $appointment) {
            if($datetime->greaterThanOrEqualTo($appointment->start_at) && $datetime->lessThan($appointment->start_at->addMinutes($appointment->duration))) {
                $isAppointment = $appointment;
            }
        }

        return $isAppointment;
    }

    public function getAppointmentsCount(): int|null
    {
        return $this->appointments?->count();
    }

    public static function getUserByMasterId(int $masterId): User|null
    {
        $master = Master::find($masterId);
        return User::where('phone', $master->getPhoneNumber())->first();
    }

    public function loadAppointmentsByPlaceId(int $placeId, Carbon $date): AppointmentService
    {
        $this->date = $date;
        $appointments = \App\Models\Appointment::with(['user.person', 'user.master'])->onlyActive()->whereDate('start_at', $date)->where('place_id', $placeId)->get();
        return $this->loadAppointments($appointments);
    }

    public function getItems($interval = 30)
    {
        $users = User::all();

        $items = [];
        for($i = $interval; $i <= 16*60+$interval; $i+= $interval) {
            unset($item);

            $nextTime = $this->date->clone()->startOfDay()->addMinutes(6*60+30)->addMinutes($i);
            $appointment = $this->getAppointment($nextTime);

            $item['date'] = $nextTime->format('Y-m-d');
            $item['time'] = $nextTime->format('H:i');
            $item['datetime'] = $nextTime->format('Y-m-d H:i');
            $item['label'] = implode(' - ', [$nextTime->format('H:i'), $nextTime->clone()->addMinutes($interval)->format('H:i')]);

            // MAX DURATION
            $item['max-duration'] = $this->getMinutesToNextAppointment($nextTime) ?: '';

            if (isset($appointment)) {
                $item['status'] = 'busy';
                $item['appointment'] = [
                    'id' => $appointment->id,
                    'start' => $appointment->start_at->format('H:i'),
                    'end' => $appointment->start_at->clone()->addMinutes($appointment->duration)->format('H:i'),
                ];
                $item['master'] = [
                    'id' => $appointment->user->master->id,
                    'full_name' => $appointment->user->master->full_name,
                    'first_name' => $appointment->user->master->person->first_name,
                ];
                $item['user'] = [
                    'id' => $users->where('phone', $appointment->user->master->getPhoneNumber())->first()?->id
                ];

            } else {
                $item['status'] = 'free';
            }

            $items[] = $item;
        }

        // ADD BREAK
        foreach ($items as $index => $item) {
            if($item['status'] == 'free' && isset($items[$index + 1]) && $items[$index + 1]['status'] == 'busy') {
                $items[$index]['status'] = 'busy break';
            }

            if($item['status'] == 'busy' && isset($items[$index + 1]) && $items[$index + 1]['status'] == 'free') {
                $items[$index + 1]['status'] = 'busy break';
            }
        }

        return $items;
    }

    public function calculateAppointmentCost(Appointment $appointment): float
    {
        $start = $appointment->start_at;
        $end = $appointment->start_at->copy()->addMinutes($appointment->duration);

        // Рассчитываем длительность аренды в часах
        $durationInMinutes = $start->diffInMinutes($end);

        // Аренда на день (более 8 часов)
        if ($durationInMinutes >= 8 * 60) {
            $amount = $appointment->place->getHourlyCost() * 8; // Стоимость аренды на 8 часов
        } else {
            // Обычная почасовая аренда
            $amount = $appointment->place->getHourlyCost() * $durationInMinutes / 60;
        }

        return $amount;
    }

    public function cancelAppointment(User $user, Appointment $appointment, ?string $cancellationReason = null): bool
    {
        // Проверяем, может ли пользователь отменить запись
        if (!$appointment->canBeCancelledByUser() && !$user->hasRole('admin')) {
            throw new \Exception('Вы не можете отменить эту запись. Свяжитесь с администратором.');
        }

        try {
            $appointment->canceled_at = now();
            $appointment->save();

            // Добавляем комментарий с причиной отмены
            if ($cancellationReason) {
                $appointment->addComment($user, $cancellationReason, Appointment::BOOKING_CANCEL_COMMENT);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error canceling appointment: ' . $e->getMessage());
            throw new \Exception('Произошла ошибка при отмене записи.');
        }
    }

    public function payForAppointment(Appointment $appointment, bool $useBalance = true): void
    {
        $amount = $amount ?? $this->calculateAppointmentCost($appointment);

        if(is_null($appointment->price)) {
            $user = $appointment->user;

            if($amount > 0) {
                if (!$useBalance) {
                    $user->deposit($amount, 'Appointment ID: ' . $appointment->id . ' <<< AUTOADD CASH');
                }
                $user->withdraw($amount, 'Appointment ID: ' . $appointment->id . ' <<< PLACE RENT');
            }

            $appointment->update([
                'price' => $amount
            ]);
        }
    }

    public function isOverlay(Appointment $appointment, bool $includeTimeBreak = true): bool
    {
        $result = false;

        $timeStart = Carbon::parse($appointment->start_at);
        $timeEnd = Carbon::parse($appointment->end_at);

        $appointments = $appointment->place->appointments()
            ->where('user_id', '!=', $appointment->user_id)
            ->whereNull('canceled_at')
            ->whereDate('start_at', $timeStart)
            ->when(isset($appointment->id), function ($query) use ($appointment) {
                $query->where('id', '!=', $appointment->id);
            })
            ->get(); // TODO where Date

        for ($checkTime = $timeStart->clone(); $checkTime < $timeEnd; $checkTime->addMinutes(10)) {

//            dump($this->isTimeAppointmentTest($checkTime, $appointments));
//            dump($this->isTimeFreeTest($checkTime, $appointments));
//            dump($this->isTimeBreakTest($checkTime, $appointments));
//            dump($checkTime);
//            dump('-------------------');

            if($this->isTimeAppointmentTest($checkTime, $appointments)) {
                $result = true;
            }

            if($includeTimeBreak && $this->isTimeBreakTest($checkTime, $appointments)) {
                $result = true;
            }
        }

        return $result;
    }

    public function mergeAppointments(Collection $appointments, $interval = 0)
    {
        try {
            $appointmentsCollection = collect($appointments)->whereNull('canceled_at')->sortBy('start_at');

            // GROUP BY USER ID AND PLACE ID
            foreach ($appointmentsCollection->groupBy(function ($appointment) {
                return 'user_id_' . $appointment->user_id . '_place_id_' . $appointment->place_id;
            }) as $groupUserAppointments) {

                // FIND CLOSEST APPOINTMENTS
                $groupUserAppointments->each(function ($appointment1) use ($groupUserAppointments) {
                    $groupUserAppointments->where('id','!=', $appointment1->id)->each(function ($appointment2) use ($appointment1, $groupUserAppointments) {

                        // MERGE AND DELETE
                        if($appointment1->end_at == $appointment2->start_at) {
                            $newDuration = $appointment1->duration + $appointment2->duration;
                            $newPrice = (isset($appointment1->price) || isset($appointment2->price)) ? $appointment1->price + $appointment2->price : null;
                            $appointment2->comments()->update([
                                'model_id' => $appointment1->id
                            ]);
                            $appointment2->delete();
                            $appointment1->update([
                                'duration' => $newDuration,
                                'price' => $newPrice,
                            ]);
                        }
                    });
                });
            }
        } catch (\Exception $e) {
            \Log::error('Error merging appointments: ' . $e->getMessage());
            throw new \Exception('Не удалось объединить записи. Пожалуйста, попробуйте еще раз.');
        }
    }

    public function isTimeSlotAvailable($placeId, Carbon $startAt, Carbon $endAt): bool
    {
        try {
            // Проверяем, нет ли других записей в это время
            $overlappingAppointments = Appointment::where('place_id', $placeId)
                ->whereNull('canceled_at')
                ->where(function($query) use ($startAt, $endAt) {
                    $query->where(function($q) use ($startAt, $endAt) {
                        // Проверяем пересечение интервалов
                        $q->where('start_at', '<', $endAt)
                          ->where(function($q) use ($startAt) {
                              $q->whereRaw('DATE_ADD(start_at, INTERVAL duration MINUTE) > ?', [$startAt]);
                          });
                    });
                })
                ->count();

            return $overlappingAppointments === 0;
        } catch (\Exception $e) {
            \Log::error('Error checking time slot availability: ' . $e->getMessage());
            throw new \Exception('Не удалось проверить доступность временного слота. Пожалуйста, попробуйте еще раз.');
        }
    }

    public function getMinDuration(): int
    {
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return self::$adminMinAppointmentDuration;
        }
        return self::$minAppointmentDuration;
    }
}
