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
        // we check on the not-used bagstart event for followers. That currently means there
        // is no way for followers to get the message that someones bag has started processing
        // It is only sent to the user itself. This is a different logic than for the bag-end
        // event.
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->content->accreditation->fencer, "bagstart")) {
            $service->generate($event->content->accreditation->fencer, $event->content, "bagstart");
        }
        $followers = $this->eventAppliesToFollowers($event->content->accreditation->fencer, "bagstart");
        if (count($followers) > 0) {
            $service->generate($event->content->accreditation->fencer, $event->content, "bagstart", $followers);
        }
    }
}
