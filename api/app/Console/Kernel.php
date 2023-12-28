<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckDirtyBadges;
use App\Jobs\CheckCleanup;
use App\Jobs\CheckSummaries;
use App\Console\Commands\QueueCheckDirtyBadges;
use App\Console\Commands\QueueCheckDirtyDocuments;
use App\Console\Commands\SendGeneralNotification;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        QueueCheckDirtyBadges::class,
        QueueCheckDirtyDocuments::class,
        SendGeneralNotification::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // check for dirty badges every 5 minutes
        $schedule->job(new CheckDirtyBadges())->everyFiveMinutes();

        // Hourly jobs
        // reschedule failed jobs
        $schedule->command('queue:retry all')->hourly();

        // check summary documents every hour to prevent build up of old documents
        $schedule->job(new CheckSummaries())->hourly();

        // Daily cleanup jobs
        // clean out old accreditations once a day
        $schedule->job(new CheckCleanup())->dailyAt('04:38');

        // drop failed jobs once a day to prevent the queue from filling up
        $schedule->command('queue:flush')->dailyAt('04:48');

        // clean the application cache to prevent botched jobs with unique settings
        // from being rescheduled
        $schedule->command('cache:clear')->dailyAt('04:58');
    }
}
