<?php

namespace App\Http\View\Composers;

use App\Models\Appointment;
use App\Models\StorageBooking;
use Illuminate\View\View;

class MastersWithDebtComposer
{
    public function compose(View $view)
    {
        $mastersWithAppointmentsDebtCount = Appointment::withDebt()
            ->distinct('user_id')
            ->count();

        $mastersWithStorageBookingDebtCount = StorageBooking::withDebt()
            ->distinct('user_id')
            ->count();

        $view->with([
            'mastersWithAppointmentsDebtCount' => $mastersWithAppointmentsDebtCount,
            'mastersWithStorageBookingDebtCount' => $mastersWithStorageBookingDebtCount]
        );
    }
}
