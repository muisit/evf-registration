<?php

namespace App\Support\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleType;
use App\Models\Registration;
use DB;

class OverviewService
{
    public Event $event;

    private $sideEventsById = [];
    private $teamEvents = [];
    private $rolesById = [];
    private $roleTypesById = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function create()
    {
        // provide an overview of the registrations for this event
        $retval = [];

        $this->initialise();

        // create an overview of participants per country per sideevent
        $rows = Registration::select('registration_event', 'registration_role', 'registration_country', 'registration_team', DB::raw('count(*) as cnt'))
            ->where('registration_mainevent', $this->event->getKey())
            ->groupBy('registration_event', 'registration_role', 'registration_country', 'registration_team')
            ->get();

        if (!emptyResult($rows)) {
            foreach ($rows as $row) {
                $countryId = $row->registration_country;
                $count = $row->cnt;
                $roleId = $row->registration_role;
                $sideEventId = $row->registration_event;

                $ckey = "c" . $countryId;
                if (!isset($retval[$ckey])) {
                    $retval[$ckey] = [];
                }
                $skey = empty($sideEventId) ? "sorg" : "s" . $sideEventId;

                if (intval($roleId) == 0 && isset($this->sidesById[$skey])) {
                    if (isset($this->teamEvents[$skey])) {
                        if (!empty($row->registration_team)) {
                            $prevcount = isset($retval[$ckey][$skey]) ? $retval[$ckey][$skey] : [0, 0];
                            $prevcount[0] += $count; // total participants
                            $prevcount[1] += 1; // each row is a team
                            $retval[$ckey][$skey] = $prevcount;
                        }
                        // else empty team name for a team event, but not an event-wide role... error?
                    }
                    else {
                        // individual athlete or participant
                        $retval[$ckey][$skey] = ($retval[$ckey][$skey] ?? 0) + $count;
                    }
                }
                else {
                    $skey = 'ssup'; // support role
                    $rkey = "r" . $roleId;
                    if (isset($this->roleById[$rkey])) {
                        // registration with a specific role
                        $role = $this->roleById[$rkey];
                        $rtkey = "r" . $role->role_type;
                        if (isset($this->roleTypeById[$rtkey])) {
                            $roleType = $this->roleTypeById[$rtkey];
                            switch ($roleType->org_declaration) {
                                default:
                                case 'Country':
                                    break;
                                case 'Org':
                                    $ckey = 'corg';
                                    $skey = $rkey;
                                    break;
                                case 'EVF':
                                    // fall through, both officials
                                case 'FIE':
                                    $ckey = 'coff';
                                    $skey = $rkey;
                                    break;
                            }
                        }
                        // else keep the ckey set to the country and the skey as ssup
                        // to mark this as a support role
                    }
                    // else: no role and no side event... this would be an error, but treat it as
                    // a generic country support role
                    $retval[$ckey][$skey] = (isset($retval[$ckey][$skey]) ? $retval[$ckey][$skey] : 0) + $tot;
                }
            }
        }
        return $retval;
    }

    private function initialise()
    {
        $this->sidesById = [];
        $this->teamEvents = [];
        foreach ($this->event->sides as $sideEvent) {
            $this->sidesById[$sideEvent->getKey()] = $sideEvent;
            if ($sideEvent->competition?->category->category_type == 'T') {
                $this->teamEvents[$sideEvent->getKey()] = $sideEvent;
            }
        }

        $this->roleTypeById = [];
        foreach (RoleType::all() as $type) {
            $this->roleTypeById[$type->getKey()] = $type;
        }

        $this->roleById = [];
        foreach (Role::all() as $role) {
            $this->roleById[$role->getKey()] = $role;
        }

    }

}