<?php

namespace App\Http\View\Composers;

use App\Models\Appointment;
use App\Models\PaymentRequirement;
use App\Models\StorageBooking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class MastersWithDebtComposer
{
    public function compose(View $view)
    {
        $mastersWithAppointmentsDebtCount = Appointment::withDebt()
            ->distinct('user_id')
            ->count();

        $mastersWithStorageBookingDebtCount = StorageBooking::withUnpaidLockerRequirement()->count();

        $usersWithAppointmentsDebtIds = PaymentRequirement::query()
            ->from('payment_requirements as pr2')
            ->join('appointments as a2', 'a2.id', '=', 'pr2.payable_id')
            ->where('pr2.payable_type', Appointment::class)
            ->where('pr2.status', 'pending')
            ->where('pr2.remaining_amount', '>', 0)
            ->whereNull('a2.deleted_at')
            ->where(function (Builder $query) {
                $query->whereNotNull('a2.canceled_at')
                    ->orWhereRaw('TIMESTAMPADD(MINUTE, a2.duration, a2.start_at) <= NOW()');
            })
            ->distinct()
            ->pluck('a2.user_id');

        $usersWithStorageDebtIds = StorageBooking::query()
            ->withUnpaidLockerRequirement()
            ->distinct()
            ->pluck('user_id');

        $debtorsCount = $usersWithAppointmentsDebtIds
            ->merge($usersWithStorageDebtIds)
            ->unique()
            ->count();

        $view->with([
            'mastersWithAppointmentsDebtCount' => $mastersWithAppointmentsDebtCount,
            'mastersWithStorageBookingDebtCount' => $mastersWithStorageBookingDebtCount,
            'debtorsCount' => $debtorsCount,
        ]);
    }
}
