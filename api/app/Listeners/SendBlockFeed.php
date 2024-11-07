<?php

namespace App\Listeners;

use App\Events\FollowEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendBlockFeed extends BasicFeedListener
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
    public function handle(FollowEvent $event): void
    {
        $service = new FeedMessageService();
        // this _should_ always evaluate to true, because the block was initiated by the fencer's device user,
        // so the test always finds a user for this fencer
        if ($this->eventAppliesToFencer($event->fencer, "block")) {
            $service->generate($event->fencer, $event->user, $event->wasCancelled ? "unblocked" : "blocked");
        }
        // always generate the block event for the blocked user
        $service->generate($event->fencer, $event->user, $event->wasCancelled ? "unblocked" : "blocked", [$event->user]);
    }
}
