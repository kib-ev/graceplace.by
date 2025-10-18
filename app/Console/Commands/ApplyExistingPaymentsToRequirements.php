<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ApplyExistingPaymentsToRequirements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:apply-to-requirements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply existing completed payments to payment requirements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to apply completed payments to requirements...');

        $paymentService = new PaymentService();
        
        // Get all completed payments
        $payments = Payment::where('status', Payment::STATUS_COMPLETED)
            ->with('payable.paymentRequirements')
            ->get();

        $this->info("Found {$payments->count()} completed payments.");

        $processed = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            if (!$payment->payable) {
                $skipped++;
                continue;
            }

            // Check if payment needs to be applied
            $requirements = $payment->payable->paymentRequirements()
                ->where('status', 'pending')
                ->where('remaining_amount', '>', 0)
                ->get();

            if ($requirements->isEmpty()) {
                $skipped++;
                continue;
            }

            $this->line("Applying payment #{$payment->id} ({$payment->amount} BYN) to requirements...");
            
            $remainingAmount = $payment->amount;

            foreach ($requirements as $requirement) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $amountToApply = min($remainingAmount, $requirement->remaining_amount);
                $requirement->applyPayment($amountToApply);
                $remainingAmount -= $amountToApply;

                $this->line("  - Applied {$amountToApply} BYN to requirement #{$requirement->id}");
            }

            $processed++;
        }

        $this->info("Processed: {$processed}, Skipped: {$skipped}");
        $this->info('Done!');

        return 0;
    }
}
