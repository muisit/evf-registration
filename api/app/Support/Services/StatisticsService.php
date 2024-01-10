<?php

namespace App\Support\Services;

use App\Models\Event;
use App\Models\Schemas\EventStatistics;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    private $event;
    private $fencers;
    private $supportRoles = 0;
    private $organisation = 0;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function generate(): EventStatistics
    {
        $retval = new EventStatistics();
        $retval->id = $this->event->getKey();
        $registrations = $this->event->registrations()->with(['fencer', 'sideEvent', 'country', 'role', 'role.type'])->get();
        $retval->registrations = $registrations->count();

        $this->fencers = [];
        $registrations->map(function ($registration) {
            $key = 'f' . $registration->registration_fencer;
            $this->fencers[$key] = $registration;
            if (empty($registration->registration_event)) {
                if (!empty($registration->registration_role)) {
                    if ($registration->role->type->org_declaration == 'Country') {
                        $this->supportRoles += 1;
                    }
                    else {
                        $this->organisation += 1;
                    }
                }
            }
        });
        $retval->participants = count($this->fencers);
        $retval->organisers = $this->organisation;
        $retval->support = $this->supportRoles;

        foreach ($this->fencers as $key => $registration) {
            switch ($registration->fencer->fencer_picture) {
                case 'Y':
                    $retval->hasNewPicture +=1;
                    break;
                case 'R':
                    $retval->hasReplacePicture += 1;
                    break;
                case 'A':
                    $retval->hasPicture += 1;
                    break;
                default:
                case 'N':
                    $retval->hasNoPicture += 1;
                    break;
            }
        }

        $retval->queue = DB::table('jobs')->where('id', '<>', null)->count();
        $retval->failed = DB::table('failed_jobs')->where('id', '<>', null)->count();
        return $retval;
    }
}
