<?php

namespace App\Console;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
//        $schedule->command('app:make-payments')->everyMinute();

        $schedule->call(function () {
            foreach (User::role('master')->with('master')->get() as $user) {
                if (!$user->master) continue;
                $fullName = implode(' ', array_filter([$user->master->last_name, $user->master->first_name]));
                if ($user->name !== $fullName) {
                    $user->update(['name' => $fullName]);
                }
            }
        })->everyMinute();

        $schedule->command('masters:update-status')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
