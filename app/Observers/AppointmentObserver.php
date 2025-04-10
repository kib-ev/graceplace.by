<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    public function creating(Appointment $appointment): bool
    {
        return !(new AppointmentService())->isOverlay($appointment);
    }

    /**
     * @throws \Exception
     */
    public function created(Appointment $appointment): void
    {
//        $paymentService = app(PaymentService::class);
//        $paymentService->createPaymentRequirement($appointment, $appointment->getExpectedPrice());
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
//        $appointment->paymentRequirements()->delete(); // todo refactor
//        $appointment->payments()->delete(); // todo refactor
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
