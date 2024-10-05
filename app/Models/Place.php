<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function isAppointment(Carbon $date) : Appointment|null
    {
        $isAppointment = null;

        foreach($this->appointments()->whereNull('canceled_at')->whereDay('start_at', $date)->get() as $appointment) {
            if($date->greaterThanOrEqualTo($appointment->start_at) && $date->lessThan($appointment->start_at->addMinutes($appointment->duration))) {
                $isAppointment = $appointment;
            }
        }

        return $isAppointment;
    }

    public function isFree(Carbon $date): bool
    {
         return !$this->isAppointment($date);
    }

    public function nextAppointment(Carbon $date) : ?Appointment
    {
        return $this->appointments()
            ->whereNull('canceled_at')
            ->whereDate('start_at', $date)
            ->where('start_at', '>=', $date)
            ->orderBy('start_at')
            ->first();
    }

    public function nextAppointmentToMinutes(Carbon $date) //: ?CarbonInterval
    {
        $appointment = $this->nextAppointment($date);

        if($appointment) {
            return CarbonInterval::minutes($date->diffInMinutes($appointment->start_at))->totalMinutes;
        }

        return null;
    }

    public function isFullDayBusy(Carbon $date): Appointment|null
    {
        return $this->appointments()
            ->whereNull('canceled_at')
            ->whereDate('start_at', $date)
            ->where('is_full_day', 1)
            ->first();
    }

    public function getHourlyCost()
    {
        return $this->price_hour;
    }
}
