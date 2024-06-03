<?php

namespace App\Console;

use App\Jobs\Notifications\SendActivitySummaries;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('import:schedule-run')->everyMinute();

        $schedule->command('activitylog:clean')->daily();
        
        $schedule->job(SendActivitySummaries::class)->hourlyAt(3);
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
