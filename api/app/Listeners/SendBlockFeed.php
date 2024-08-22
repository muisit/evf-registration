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
        // this _should_ always evaluate to true, because the block was initiated by the fencer's device user
        if ($this->eventAppliesToFencer($event->fencer, "block")) {
            $service->generate($event->fencer, $event->user, $event->wasCancelled ? "unblock" : "block", $event->fencer->user);
        }
        // always generate the block event for the blocked user
        $service->generate($event->fencer, $event->user, $event->wasCancelled ? "unblock" : "block");
    }
}
