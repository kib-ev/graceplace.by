<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\StorageBooking;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class CompleteLockerCashPayments extends Command
{
    protected $signature = 'locker:complete-cash-payments {--dry-run : Показать, что будет сделано, без изменений}';
    protected $description = 'Пометить pending платежи за локер (METHOD_CASH) как completed';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $payments = Payment::where('payable_type', StorageBooking::class)
            ->where('status', Payment::STATUS_PENDING)
            ->where('payment_method', Payment::METHOD_CASH)
            ->get();

        $count = $payments->count();
        if ($count === 0) {
            $this->info('Нет pending кассовых платежей за локер.');
            return 0;
        }

        $totalAmount = $payments->sum('amount');
        $this->info("Найдено {$count} платежей на сумму {$totalAmount} BYN.");

        if ($dryRun) {
            $this->warn('Режим --dry-run: изменения не применяются.');
            return 0;
        }

        $paymentService = new PaymentService();
        foreach ($payments as $payment) {
            $paymentService->changePaymentStatus($payment, Payment::STATUS_COMPLETED);
        }

        $this->info("Помечено {$count} платежей как completed.");
        return 0;
    }
}
