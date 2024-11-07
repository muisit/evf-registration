<?php

namespace App\Listeners;

use App\Events\FollowEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendFollowFeed extends BasicFeedListener
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
        // only send the 'you are followed' feed if there is a device-user attached to the fencer
        if ($this->eventAppliesToFencer($event->fencer, "follow")) {
            $service->generate($event->fencer, $event->user, $event->wasCancelled ? "unfollow" : "follow");
        }
        // always send the follow event for the follower
        $service->generate($event->fencer, $event->user, $event->wasCancelled ? "unfollow" : "follow", [$event->user]);
    }
}
