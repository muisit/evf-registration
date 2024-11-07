<?php

namespace App\Listeners;

use App\Events\ProcessEndEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendBagFinishedFeed extends BasicFeedListener
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
    public function handle(ProcessEndEvent $event): void
    {
        // the bag-end event is linked to the checkout-capability here, so people that are allowed
        // to see the checkout event (bag was checked out) also see the bagend (bag is available for checkout)
        // event
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->content->accreditation->fencer, "checkout")) {
            $service->generate($event->content->accreditation->fencer, $event->content, "bagend");
        }
        $followers = $this->eventAppliesToFollowers($event->content->accreditation->fencer, "checkout");
        if (count($followers) > 0) {
            $service->generate($event->content->accreditation->fencer, $event->content, "bagend", $followers);
        }
    }
}
