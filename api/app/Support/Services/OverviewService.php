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
    public array $overview;

    public $sideEventsById = [];
    public $teamEvents = [];
    public $rolesById = [];
    public $roleTypesById = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function create()
    {
        // provide an overview of the registrations for this event
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
                if (!empty($countryId) && !isset($this->overview[$ckey])) {
                    $this->overview[$ckey] = [];
                }
                $skey = empty($sideEventId) ? "sorg" : "s" . $sideEventId;

                if (intval($roleId) == 0 && isset($this->sidesById[$skey]) && !empty($countryId)) {
                    $this->addEventRole($ckey, $skey, $count, $row->registration_team);
                }
                else {
                    $this->addSupportRole($roleId, $ckey, $count);
                }
            }
        }
        return $this->overview;
    }

    public function addEventRole(string $ckey, string $skey, int $tot, ?string $team = null)
    {
        if (isset($this->teamEvents[$skey])) {
            if (!empty($team)) {
                $prevcount = isset($this->overview[$ckey][$skey]) ? $this->overview[$ckey][$skey] : [0, 0];
                $prevcount[0] += $tot; // total participants
                $prevcount[1] += 1; // each row is a team
                $this->overview[$ckey][$skey] = $prevcount;
            }
            // else empty team name for a team event, but not an event-wide role... error?
        }
        else {
            // individual athlete or participant
            $prevcount = isset($this->overview[$ckey][$skey]) ? $this->overview[$ckey][$skey] : [0, 0];
            $prevcount[0] += $tot; // total participants
            $this->overview[$ckey][$skey] = $prevcount;
        }
    }

    public function addSupportRole(int $roleId, string $ckey, int $tot)
    {
        $skey = 'ssup'; // support role
        $rkey = "r" . $roleId;
        if (isset($this->roleById[$rkey])) {
            // registration with a specific role (which all registrations ought to have)
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
        else {
            // else: no role, possibly an invitation for a gala side-event
            // treat this as a 'corg' support role
            $ckey = 'corg';
        }

        // finally add the count to the determined key
        $prevcount = isset($this->overview[$ckey][$skey]) ? $this->overview[$ckey][$skey] : [0, 0];
        $prevcount[0] += $tot; // total participants
        $this->overview[$ckey][$skey] = $prevcount;
    }

    public function initialise()
    {
        $this->overview = [];
        $this->sidesById = [];
        $this->teamEvents = [];
        foreach ($this->event->sides as $sideEvent) {
            $this->sidesById['s' . $sideEvent->getKey()] = $sideEvent;
            if ($sideEvent->competition?->category->category_type == 'T') {
                $this->teamEvents['s' . $sideEvent->getKey()] = $sideEvent;
            }
        }

        $this->roleTypeById = [];
        foreach (RoleType::all() as $type) {
            $this->roleTypeById['r' . $type->getKey()] = $type;
        }

        $this->roleById = [];
        foreach (Role::all() as $role) {
            $this->roleById['r' . $role->getKey()] = $role;
        }
    }
}
