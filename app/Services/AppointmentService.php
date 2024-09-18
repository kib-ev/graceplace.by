<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Person;
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

    public function loadAppointments(Collection $appointments): AppointmentService
    {
        $this->appointments = $appointments->sortBy('date')->whereNull('canceled_at');
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
            if($appointment->full_day) {
                $result = false;
            }

            $breakTime = $this->getBreakTime($appointment);

            if($datetime->greaterThanOrEqualTo($appointment->date->subMinutes($breakTime))
                && $datetime->lessThan($appointment->date->addMinutes($appointment->duration + $breakTime))) {
                $result = false;
            }
        });

        return $result;
    }

    public function isTimeAppointment(Carbon $datetime): bool
    {
        $result = true;

        $this->appointments->each(function ($appointment) use (&$result, $datetime) {
            if($appointment->full_day) {
                $result = false;
            }

            if($datetime->greaterThanOrEqualTo($appointment->date) && $datetime->lessThan($appointment->date->addMinutes($appointment->duration))) {
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
        return $this->appointments->where('date', '>=', $datetime)->first();
    }

    public function getMinutesToNextAppointment(Carbon $datetime): int|null
    {
        $appointment = $this->getNextAppointment($datetime);

        if($appointment) {
            $interval = CarbonInterval::minutes($datetime->diffInMinutes($appointment->date));

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
            if($datetime->greaterThanOrEqualTo($appointment->date) && $datetime->lessThan($appointment->date->addMinutes($appointment->duration))) {
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
            if($appointment->full_day) {
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
        $appointments = \App\Models\Appointment::onlyActive()->whereDate('date', $date)->where('place_id', $placeId)->get();
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
                    'start' => $appointment->date->format('H:i'),
                    'end' => $appointment->date->clone()->addMinutes($appointment->duration)->format('H:i'),
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
}
