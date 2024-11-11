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
        $schedule->command('sync:owner-associations')->daily();
        $schedule->command('telescope:clear')->daily();
        $schedule->command('app:deactivate-vendor')->daily();
        $schedule->command('budget:clean-imports')->daily();
        $schedule->command('app:contract-renewal-mail')->daily();
        $schedule->command('app:trade-licence-expiry-mail')->daily();
        $schedule->command('app:risk-policy-expiry-mail')->daily();
        $schedule->command('app:update-delinquent-owners')->dailyAt('01:00');
        $schedule->command('app:update-delinquent-owners-receipts')->dailyAt('02:00');
        $schedule->command('app:announcement-notifications')->everyMinute();
        $schedule->command('app:poll-notifications')->everyMinute()->withoutOverlapping();
        $schedule->command('app:resident-move-out')->everyMinute();
        $schedule->command('app:sub-contract-renewal-reminder')->daily();
        $schedule->command('app:compliance-document-notification')->daily();
        $schedule->command('fetch:invoices')->daily();
        $schedule->command('dispatch:receipt-fetch')->daily();
        $schedule->command('app:moveout-notification')->daily();
        $schedule->command('app:tenant-expiry-notification')->daily();
        $schedule->command('buildings:detach-expired')->daily();
        $schedule->command('buildings:handle-pm-detachments')->daily();
        $schedule->command('invoices:check-overdue')->daily();
        // $schedule->command('receipts:check-overdue')->daily();


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
