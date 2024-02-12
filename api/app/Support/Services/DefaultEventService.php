<?php

namespace App\Support\Services;

use App\Support\Contracts\EVFUser;
use App\Models\Event;

class DefaultEventService
{
    public static function determineEvent(EVFUser $user): ?Event
    {
        $event = null;
        \Log::debug("user roles are " . json_encode($user->getAuthRoles()));
        if ($user->hasRole("organisation")) {
            $roles = $user->rolesLike("organisation:");
            $events = [];
            foreach ($roles as $role) {
                $eid = intval(substr($role, 13));
                $event = Event::where("event_id", $eid)->first();
                if (($event->useRegistrationApplication() || $event->useAccreditationApplication()) && !$event->isFinished()) {
                    $events[] = $event;
                }
            }

            if (count($events) == 1) {
                $event = $events[0];
            }
        }
        return $event;
    }
}
