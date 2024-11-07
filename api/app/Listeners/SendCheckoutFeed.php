<?php

namespace App\Listeners;

use App\Events\CheckoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendCheckoutFeed extends BasicFeedListener
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
    public function handle(CheckoutEvent $event): void
    {
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->content->accreditation->fencer, "checkout")) {
            $service->generate($event->content->accreditation->fencer, $event->content, "checkout");
        }
        $followers = $this->eventAppliesToFollowers($event->content->accreditation->fencer, "checkout");
        if (count($followers) > 0) {
            $service->generate($event->content->accreditation->fencer, $event->content, "checkout", $followers);
        }
    }
}
