<?php

namespace App\Support\Services;

use App\Models\Fencer;
use App\Models\Event;
use App\Models\Registration;
use DateTimeImmutable;

class GeneralNotificationService
{
    public function generate()
    {
        $generalStatistics = $this->generalStatistics();
        $events = $this->eventData();

        $lines = array_merge($generalStatistics, $events);
        return implode('<br/>', $lines);
    }

    public function generalStatistics()
    {
        $numberOfFencers = Fencer::where('fencer_id', '>', 0)->count();
        return [
            "Number of unique fencers: $numberOfFencers"
        ];
    }

    public function eventData()
    {
        $retval = [];
        $now = (new DateTimeImmutable())->format('Y-m-d');
        $events = Event::where('event_open', '>', $now)->orderBy('event_open')->get();

        foreach ($events as $event) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $event->event_open);
            if ($date === false) {
                $date = 'invalid date';
            }
            else {
                $date = $date->format('Y-m-d');
            }
            $retval[] = "Event " . $event->event_name . " at " . $date;

            $regCount = Registration::where('registration_mainevent', $event->getKey())->count();
            $retval[] = "Current registrations: $regCount";
        }
        return $retval;
    }
}