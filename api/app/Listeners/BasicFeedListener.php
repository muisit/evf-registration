<?php

namespace App\Listeners;

class BasicFeedListener
{
    protected function eventAppliesToFencer($fencer, $eventType)
    {
        // we skip eventType for now: all events are broadcast to the user always
        // there is currently no option to indicate you want more or less of these
        // messages in your own feed, only if you want to allow followers to get
        // these messages
        return $fencer->user()?->exists() ? true : false;
    }

    protected function eventAppliesToFollowers($fencer, $eventType): array
    {
        $retval = [];
        // just to be sure, we recheck that this fencer allows triggering this kind of event
        \Log::debug("event $eventType applies to followers");
        if ($fencer->triggersEvent($eventType)) {
            foreach ($fencer->followers as $follower) {
                // this checks if the follower is perhaps blocked and, if not, if the follower
                // is interested in this kind of event
                // The block status is recorded with the destination user, although it is a
                // descision of the followed fencer/user
                if ($follower->triggersOnEvent($eventType)) {
                    $retval[] = $follower;
                }
            }
        }
        return $retval;
    }
}
