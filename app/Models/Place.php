<?php

namespace App\Models;

use Carbon\Carbon;
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

        foreach($this->appointments()->whereDay('date', $date)->get() as $appointment) {
            if($date->greaterThanOrEqualTo($appointment->date) && $date->lessThan($appointment->date->addMinutes($appointment->duration))) {
                $isAppointment = $appointment;
            }
        }

        return $isAppointment;
    }

    public function isFree(Carbon $date): bool
    {
         return !$this->isAppointment($date);
    }
}
