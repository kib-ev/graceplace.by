<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateMastersStatus extends Command
{
    protected $signature = 'masters:update-status';

    protected $description = 'Update masters status based on their activity';

    public function handle(): int
    {
        $this->info('Starting masters status update...');

        $inactiveDays = 100;
        $inactiveDate = now()->subDays($inactiveDays);

        $masters = User::role('master')
            ->with(['appointments' => function ($query) {
                $query->whereNull('canceled_at')
                    ->orderBy('start_at', 'desc');
            }])
            ->get();

        $activated = 0;
        $deactivated = 0;

        foreach ($masters as $master) {
            $lastAppointment = $master->appointments->first();

            if ($lastAppointment && $lastAppointment->start_at->isAfter($inactiveDate)) {
                if (!$master->is_active) {
                    $master->update(['is_active' => true]);
                    $activated++;
                }
            } else {
                if ($master->is_active) {
                    $master->update(['is_active' => false]);
                    $deactivated++;
                }
            }
        }

        $this->info("Masters activated: {$activated}");
        $this->info("Masters deactivated: {$deactivated}");
        $this->info('Masters status update completed.');

        return Command::SUCCESS;
    }
}

