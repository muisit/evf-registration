<?php

namespace App\Listeners;

use App\Events\AccreditationHandoutEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendHandoutFeed extends BasicFeedListener
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
    public function handle(AccreditationHandoutEvent $event): void
    {
        $service = new FeedMessageService();
        if ($this->eventAppliesToFencer($event->accreditation->fencer, "handout")) {
            $service->generate($event->accreditation->fencer, $event->accreditation, "handout");
        }
        $followers = $this->eventAppliesToFollowers($event->accreditation->fencer, "handout");
        if (count($followers)) {
            $service->generate($event->accreditation->fencer, $event->accreditation, "handout", $followers);
        }
    }
}
