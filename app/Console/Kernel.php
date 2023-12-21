<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('sync:owner-associations')->hourly();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('app:deactivate-vendor')->daily();
        $schedule->command('budget:clean-imports')->daily();
        $schedule->command('app:contract-renewal-mail')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
