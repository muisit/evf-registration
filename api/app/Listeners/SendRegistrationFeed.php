<?php

namespace App\Listeners;

use App\Events\RegisterForEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\Services\FeedMessageService;

class SendRegistrationFeed extends BasicFeedListener
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
    public function handle(RegisterForEvent $event): void
    {
        $service = new FeedMessageService();
        // Fencer registered for a specific competition
        if ($this->eventAppliesToFencer($event->fencer, "register")) {
            $service->generate($event->fencer, $event->competition, $event->wasCancelled ? "unregister" : "register", $event->ranking->fencer->user);
        }
        if ($this->eventAppliesToFollowers($event->fencer, "register")) {
            $service->generate($event->fencer, $event->competition, $event->wasCancelled ? "unregister" : "register");
        }
    }
}
