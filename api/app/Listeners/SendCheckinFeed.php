<?php

namespace App\Listeners;

use App\Events\CheckinEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendCheckinFeed extends BasicFeedListener
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
    public function handle(CheckinEvent $event): void
    {
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->content->fencer, "checkin")) {
            $service->generate($event->content->fencer, $event->content, "checkin", $event->content->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->content->fencer, "checkin")) {
            $service->generate($event->content->fencer, $event->content, "checkin");
        }
    }
}
