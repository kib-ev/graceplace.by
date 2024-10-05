<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Person;
use App\Models\Place;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AppointmentService
{
    public static int $defaultTimeStep = 30; // for appointment and duration
    public static int $defaultBreakTime = 30;
    public static int $minAppointmentDuration = 60;

    protected Collection|null $appointments = null;
    protected Carbon|null $date = null;

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
            $master = AppointmentService::getMasterByUserId(auth()->id());
            if($appointment->master_id == $master?->id) {
                return 0;
            }
        }

        return self::$defaultBreakTime;
    }

    public function isTimeFree(Carbon $datetime): bool
    {
        $result = true;

        $this->appointments->each(function ($appointment) use (&$result, $datetime) {
            if($appointment->is_full_day) {
                $result = false;
            }

            $breakTime = $this->getBreakTime($appointment);

            if($datetime->greaterThanOrEqualTo($appointment->start_at->subMinutes($breakTime))
                && $datetime->lessThan($appointment->start_at->addMinutes($appointment->duration + $breakTime))) {
                $result = false;
            }
        });

        return $result;
    }

    public function isTimeAppointment(Carbon $datetime): bool
    {
        $result = true;

        $this->appointments->each(function ($appointment) use (&$result, $datetime) {
            if($appointment->is_full_day) {
                $result = false;
            }

            if($datetime->greaterThanOrEqualTo($appointment->start_at) && $datetime->lessThan($appointment->start_at->addMinutes($appointment->duration))) {
                $result = false;
            }
        });

        return $result;
    }

    public function isTimeBreak(Carbon $datetime): bool
    {
        return !(!$this->isTimeAppointment($datetime) && !$this->isTimeFree($datetime));
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

        if($this->hasFullDayAppointment($datetime)) {
            return $this->hasFullDayAppointment($datetime);
        }

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

    public function hasFullDayAppointment(Carbon $datetime): Appointment|null
    {
        $result = null;

        $this->appointments->each(function ($appointment) use (&$result, $datetime) {
            if($appointment->is_full_day) {
                $result = $appointment;
            }
        });

        return $result;
    }

    public static function getMasterByUserId(int $userId): Master|null
    {
        if(auth()->id() == 1) return null;

        $master = Cache::remember('user_'.$userId.'_master', 60*60, function () use ($userId) {
            $user = User::find($userId);

            if ($user) {
                $person = Person::whereHas('phones', function ($query) use ($user) {
                    $query->where('number', $user->phone);
                })->first();

                return $person?->master;
            }

            return null;
        });

//        Log::info($userId.'_'.$master?->id);

        return $master ?? null ;
    }

    public static function getUserByMasterId(int $masterId): User|null
    {
        $master = Master::find($masterId);
        return User::where('phone', $master->getPhoneNumber())->first();
    }

    public function loadAppointmentsByPlaceId(int $placeId, Carbon $date): AppointmentService
    {
        $this->date = $date;
        $appointments = \App\Models\Appointment::onlyActive()->whereDate('start_at', $date)->where('place_id', $placeId)->get();
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
                    'id' => $appointment->master->id,
                    'full_name' => $appointment->master->full_name,
                    'first_name' => $appointment->master->person->first_name,
                ];
                $item['user'] = [
                    'id' => $users->where('phone', $appointment->master->getPhoneNumber())->first()?->id
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

        if ($durationInMinutes >= 8 * 60) {
            // Аренда на целый день
            $appointment->is_full_day = true;
            $appointment->cost = $appointment->place->getHourlyCost() * 8; // Стоимость аренды на 8 часов
        } else {
            // Обычная почасовая аренда
            $appointment->is_full_day = false;
            $appointment->cost = $appointment->place->getHourlyCost() * $durationInMinutes / 60;
        }

        return $appointment->cost;
    }
}
