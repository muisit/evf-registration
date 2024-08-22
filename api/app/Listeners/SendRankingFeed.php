<?php

namespace App\Listeners;

use App\Events\RankingUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendRankingFeed extends BasicFeedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RankingUpdate $event): void
    {
        $service = new FeedMessageService();
        // There was a rankingupdate for a specific fencer
        if ($this->eventAppliesToFencer($event->ranking->fencer, "ranking")) {
            $service->generate($event->ranking->fencer, $event->ranking, "ranking", $event->ranking->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->ranking->fencer, "ranking")) {
            $service->generate($event->ranking->fencer, $event->ranking, "ranking");
        }
    }
}
