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
        if ($this->eventAppliesToFencer($event->content->fencer, "checkout")) {
            $service->generate($event->content->fencer, $event->content, "checkout", $event->content->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->content->fencer, "checkout")) {
            $service->generate($event->content->fencer, $event->content, "checkout");
        }
    }
}
