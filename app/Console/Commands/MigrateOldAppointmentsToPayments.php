<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class MigrateOldAppointmentsToPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:migrate-to-payments
                            {limit=50 : Number of appointments to migrate}
                            {--dry-run : Preview migration without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old appointments to new payment requirements and payments system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->argument('limit');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
        }

        $this->info("Starting migration of up to {$limit} old appointments...");

        // Find earliest appointments without payment requirements but with old price field
        // Note: This assumes appointments table still has 'price' column from old system
        // We fetch more records than needed and then filter, because some may not have price set
        $appointments = Appointment::withTrashed()
            ->whereDoesntHave('paymentRequirements')
            ->whereNotNull('price')
            ->orderBy('created_at', 'asc')
            ->with('comments')
            ->get()
            ->take($limit); // Then take only the requested amount

        if ($appointments->isEmpty()) {
            $this->info('No appointments found to migrate.');
            return 0;
        }

        $this->info("Found {$appointments->count()} appointments to migrate.");

        if ($dryRun) {
            $this->previewMigration($appointments);
            return 0;
        }

        $paymentService = new PaymentService();
        $migrated = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($appointments->count());
        $progressBar->start();

        foreach ($appointments as $appointment) {
            try {
                // Detect payment method from comments
                $paymentMethod = Payment::METHOD_CASH;
                $paymentNote = null;
                $eripComment = null;

                foreach ($appointment->comments as $comment) {
                    if (stripos($comment->text, 'ЕРИП') !== false || stripos($comment->text, 'ERIP') !== false) {
                        $paymentMethod = Payment::METHOD_SERVICE;
                        $eripComment = $comment->text;
                        break;
                    }
                }

                if ($eripComment) {
                    $paymentNote = $eripComment;
                }

                // Create payment requirement
                $requirement = $paymentService->createPaymentRequirement(
                    $appointment,
                    $appointment->price,
                    30,
                    $appointment->created_at,
                    [
                        'expected_amount' => $appointment->price,
                        'remaining_amount' => 0, // Will be set to 0 as it's already paid
                        'status' => 'paid' // Mark as paid immediately
                    ]
                );

                // Create payment with end time of appointment
                $payment = $paymentService->createPayment(
                    $appointment,
                    $appointment->price,
                    $paymentMethod,
                    $appointment->end_at, // Payment date = end time of appointment
                    $paymentNote
                );

                // Mark payment as completed
                $paymentService->changePaymentStatus($payment, Payment::STATUS_COMPLETED);

                $migrated++;

                $this->newLine();
                $this->line("✓ Appointment #{$appointment->id}: {$appointment->price} BYN ({$paymentMethod})");

            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("✗ Appointment #{$appointment->id}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Migrated', $migrated],
                ['Errors', $errors],
                ['Total', $appointments->count()],
            ]
        );

        return 0;
    }

    protected function previewMigration($appointments)
    {
        $this->newLine();
        $this->info("Preview of appointments to be migrated:");
        $this->newLine();

        $tableData = [];
        $cashCount = 0;
        $serviceCount = 0;
        $totalAmount = 0;

        foreach ($appointments as $appointment) {
            $paymentMethod = Payment::METHOD_CASH;
            $hasErip = false;

            foreach ($appointment->comments as $comment) {
                if (stripos($comment->text, 'ЕРИП') !== false || stripos($comment->text, 'ERIP') !== false) {
                    $paymentMethod = Payment::METHOD_SERVICE;
                    $hasErip = true;
                    break;
                }
            }

            if ($paymentMethod === Payment::METHOD_CASH) {
                $cashCount++;
            } else {
                $serviceCount++;
            }

            $totalAmount += $appointment->price;

            $tableData[] = [
                $appointment->id,
                $appointment->created_at->format('d.m.Y'),
                number_format($appointment->price, 2),
                $paymentMethod,
                $hasErip ? '✓' : '',
                $appointment->comments->count(),
            ];
        }

        $this->table(
            ['ID', 'Created', 'Price', 'Method', 'Has ЕРИП', 'Comments'],
            $tableData
        );

        $this->newLine();
        $this->info("Summary:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total appointments', $appointments->count()],
                ['Cash payments', $cashCount],
                ['Service (ЕРИП) payments', $serviceCount],
                ['Total amount', number_format($totalAmount, 2) . ' BYN'],
            ]
        );

        $this->newLine();
        $this->comment("To perform actual migration, run without --dry-run flag:");
        $this->line("php artisan appointments:migrate-to-payments {$appointments->count()}");
    }
}
