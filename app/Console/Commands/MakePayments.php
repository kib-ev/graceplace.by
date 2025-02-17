<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\StorageBooking;
use App\Services\AppointmentService;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MakePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // APPOINTMENTS
        $appointments = Appointment::whereNull('price')->whereNull('canceled_at')->get();

        foreach ($appointments as $appointment) {
            $finishedAt = $appointment->start_at->clone()->addMinutes($appointment->duration);

            if ($appointment->user->getBalance() > 0 && $finishedAt < now()) {
                Log::info('Payment for appointment ID ' . $appointment->id);

                $amount = (new AppointmentService())->calculateAppointmentCost($appointment);

                $appointment->user->withdraw($amount, 'Автоматическая оплата аренды места (Appointment ID: ' . $appointment->id . ')');

                $appointment->update([
                    'price' => $amount
                ]);
            }
        }

        // STORAGE BOOKING
        $storageBookings = StorageBooking::whereHas('user', function ($query) {
            $query->whereRaw('real_balance + bonus_balance > 0');
        })->get();

        foreach ($storageBookings as $storageBooking) {
            /* @var $storageBooking StorageBooking */

            if($storageBooking->daysLeft() <= 0) {
                $userBalance = $storageBooking->user->getBalance();
                $storageBookingPrice = $storageBooking->cell->cost_per_month;

                if ($userBalance > 0 && $userBalance >= $storageBookingPrice) {
                    $storageBooking->user->withdraw($storageBookingPrice, 'Автоматическая оплата аренды локера (ячейка: ' . $storageBooking->cell->number . ')');
                    $storageBooking->extend();
                }
            }
        }
    }
}
