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

        $schedule->call(function () { // TODO TEMP
            foreach (User::role('master')->with(['master.person'])->get() as $user) {
                $fullName = implode(' ', [$user->master->person->last_name, $user->master->person->first_name]);
                if ($user->name != $fullName) {
                    $user->update([
                        'name' => implode(' ', [$user->master->person->last_name, $user->master->person->first_name])
                    ]);
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
