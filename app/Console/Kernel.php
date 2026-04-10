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
        $schedule->command('app:expire-users')->everyMinute();
        $schedule->command('app:daily-backup')->dailyAt('02:00');
        $schedule->command('app:backup-database')->dailyAt('02:00');
        $schedule->command('app:sync-nas')->hourly();
        $schedule->command('subscriptions:check-expiry')->hourly();

        // Process any pending router commands that haven't been picked up by
        // the 1-second sync (safety net for routers that come back online).
        $schedule->command('app:process-router-commands')->everyMinute();

        // Prune sync logs older than 24 hours to keep the table manageable.
        $schedule->command('app:cleanup-sync-logs')->daily();
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