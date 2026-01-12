<?php

namespace App\Jobs;

use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Notification;
use App\Support\Services\RankingStoreService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

// This job recreates the most recent ranking based on the most recent events
class CreateRanking extends Job implements ShouldBeUnique
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function uniqueId(): string
    {
        return self::class;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new RankingStoreService();
        $results = $service->handle();

        $content = "New rankings were calculated for the different categories:<br/><ul>";
        foreach ($results as $ranking) {
            $count = $ranking->positions()->count();
            $event = $ranking->event->event_name;
            $date = $ranking->created_at->format('Y-m-d');
            $cat = $ranking->category->category_abbr;
            $wpn = $ranking->weapon->weapon_abbr;

            $content .= "<li>$wpn$cat: $event at $date: $count entries</li>";
        }
        $content .= "</ul>";

        $notification = new GenericNotification($content);
        Notification::route('mail', 'webmaster@veteransfencing.eu')->notify($notification);
    }
}
