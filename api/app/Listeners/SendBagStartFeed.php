<?php

namespace App\Listeners;

use App\Events\ProcessStartEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendBagStartFeed extends BasicFeedListener
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
    public function handle(ProcessStartEvent $event): void
    {
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->content->fencer, "checkin")) {
            $service->generate($event->content->fencer, $event->content, "bagstart", $event->content->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->content->fencer, "checkin")) {
            $service->generate($event->content->fencer, $event->content, "bagstart");
        }
    }
}
