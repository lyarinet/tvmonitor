<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These cron jobs are run in the background by a process manager like Supervisor or Laravel Horizon.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Monitor all streams every minute
        $schedule->command('streams:monitor --all')->everyMinute();
        
        // Generate thumbnails for all active input streams every 5 minutes
        $schedule->command('streams:thumbnails --all')->everyFiveMinutes();
        
        // Add any other scheduled tasks here
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 