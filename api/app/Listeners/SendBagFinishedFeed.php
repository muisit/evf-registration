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
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->content->fencer, "checkout")) {
            $service->generate($event->content->fencer, $event->content, "bagend", $event->content->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->content->fencer, "checkout")) {
            $service->generate($event->content->fencer, $event->content, "bagend");
        }
    }
}
