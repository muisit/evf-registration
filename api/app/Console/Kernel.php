<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckDirtyBadges;
use App\Jobs\CheckCleanup;
use App\Console\Commands\QueueCheckDirtyBadges;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        QueueCheckDirtyBadges::class
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

        // reschedule failed jobs
        $schedule->command('queue:retry all')->hourly();

        // clean out old accreditations once a day
        $schedule->job(new CheckCleanup())->daily();
    }
}
