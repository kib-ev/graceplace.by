<?php

namespace App\Console\Commands;

use App\Models\StorageBooking;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessStorageBookingAutoRenewals extends Command
{
    protected $signature = 'storage-bookings:process-auto-renewal';

    protected $description = 'Автопродление истёкших броней локера';

    public function handle(PaymentService $paymentService): int
    {
        $renewed = 0;

        StorageBooking::query()
            ->with('cell')
            ->whereNull('finished_at')
            ->where('auto_renewal', true)
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($paymentService, &$renewed) {
                foreach ($bookings as $booking) {
                    if (! $booking->start_at) {
                        continue;
                    }

                    $periodEnd = $booking->start_at->copy()->addDays((int) $booking->duration);
                    if ($periodEnd->isFuture()) {
                        continue;
                    }

                    try {
                        DB::transaction(function () use ($booking, $paymentService, &$renewed) {
                            $locked = StorageBooking::whereKey($booking->id)
                                ->lockForUpdate()
                                ->first();

                            if (
                                ! $locked
                                || $locked->finished_at !== null
                                || ! $locked->auto_renewal
                                || ! $locked->start_at
                            ) {
                                return;
                            }

                            $lockedEnd = $locked->start_at->copy()->addDays((int) $locked->duration);
                            if ($lockedEnd->isFuture()) {
                                return;
                            }

                            $locked->loadMissing('cell');
                            if (! $locked->cell) {
                                return;
                            }

                            StorageBooking::where('model_id', $locked->model_id)
                                ->where('model_class', $locked->model_class)
                                ->whereNull('finished_at')
                                ->where('id', '!=', $locked->id)
                                ->update(['finished_at' => now()]);

                            $locked->update(['finished_at' => now()]);

                            $newStartAt = $locked->start_at->copy()->addDays((int) $locked->duration);
                            $amount = (float) ($locked->cell->cost_per_month ?? 0);

                            $newBooking = StorageBooking::create([
                                'user_id' => $locked->user_id,
                                'model_class' => $locked->model_class,
                                'model_id' => $locked->model_id,
                                'start_at' => $newStartAt,
                                'duration' => 30,
                                'auto_renewal' => true,
                            ]);

                            if ($amount > 0) {
                                $paymentService->createPaymentRequirement($newBooking, $amount, 30, $newStartAt);
                            }

                            $renewed++;
                        });
                    } catch (\Throwable $e) {
                        $this->error("Бронь {$booking->id}: {$e->getMessage()}");
                        report($e);
                    }
                }
            });

        if ($renewed > 0) {
            $this->info("Автопродление: обработано записей — {$renewed}");
        }

        return self::SUCCESS;
    }
}
