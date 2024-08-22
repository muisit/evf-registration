<?php

namespace App\Listeners;

use App\Events\ResultUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendResultFeed extends BasicFeedListener
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
    public function handle(ResultUpdate $event): void
    {
        $service = new FeedMessageService();
        // There was a result update for a specific fencer
        if ($this->eventAppliesToFencer($event->result->fencer, "result")) {
            $service->generate($event->result->fencer, $event->result, "result", $event->result->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->result->fencer, "result")) {
            $service->generate($event->result->fencer, $event->result, "result");
        }
    }
}
