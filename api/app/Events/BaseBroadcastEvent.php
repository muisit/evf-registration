<?php
 
namespace App\Events;
 
use App\Models\Accreditation;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use Carbon\Carbon;

class BaseBroadcastEvent
{
    protected function getDatesOfRegistration(Fencer $fencer, Event $event)
    {
        $dates = [];
        $registrations = Registration::where('registration_fencer', $fencer->getKey())
            ->where('registration_mainevent', $event->getKey())
            ->with('sideEvent')
            ->get();
        foreach ($registrations as $registration) {
            if (!empty($registration->sideEvent)) {
                $dt = (new Carbon($registration->sideEvent->starts))->format('D d');
                $dates[$dt] = true;
            }
        }
        return array_keys($dates);
    }

    protected function getCountryOfRegistration(Fencer $fencer, Event $event)
    {
        return $fencer->getCountryOfRegistration($event);
    }

    protected function getBadgesOfFencer(Fencer $fencer, Event $event)
    {
        $badges = [];
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        foreach ($accreditations as $accreditation) {
            $badges[] = $accreditation->getFullAccreditationId();
        }
        return $badges;
    }
}
