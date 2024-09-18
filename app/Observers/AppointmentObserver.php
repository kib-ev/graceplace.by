<?php

namespace App\Observers;

use App\Models\Appointment;

class AppointmentObserver
{
    public function creating(Appointment $appointment): bool
    {
        return !$appointment->isOverlay() && $appointment->duration > 0;
    }

    public function created(Appointment $appointment): void
    {
        $appointment->mergeWithClosest();
    }

    public function updating(Appointment $appointment): bool
    {
        return !$appointment->isOverlay() && $appointment->duration > 0;
    }

    public function updated(Appointment $appointment): void
    {
        //
    }

    public function deleted(Appointment $appointment): void
    {
        //
    }

    public function restored(Appointment $appointment): void
    {
        //
    }

    public function forceDeleted(Appointment $appointment): void
    {
        //
    }
}
