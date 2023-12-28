<?php

namespace App\Support\Services;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Category;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Models\SideEvent;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class AccreditationCreateService
{
    private array $sidesById = [];
    private array $rolesById = [];
    private array $templates = [];
    private array $roleTypeById = [];
    private array $dateRoles = [];

    public function __construct(Fencer $fencer, Event $event)
    {
        $this->fencer = $fencer;
        $this->event = $event;
        $this->initialise();
    }

    public function handle()
    {
        $registrations = Registration::where('registration_fencer', $this->fencer->getKey())->where('registration_mainevent', $this->event->getKey())->get();
        $this->checkRolesAndDates($registrations);
        $accreditations = [];

        foreach ($this->templates as $template) {
            $accreditation = $this->createAccreditationForTemplate($template);
            if (!empty($accreditation)) {
                $accreditations[] = $accreditation;
            }
        }
        return $accreditations;
    }

    private function initialise()
    {
        $sides = $this->event->sides()->with('competition')->get();
        foreach ($this->event->sides as $s) {
            $this->sidesById["s" . $s->getKey()] = $s;
        }

        $roles = Role::get();
        foreach ($roles as $r) {
            $this->rolesById["r" . $r->getKey()] = $r;
        }

        $templates = AccreditationTemplate::where('event_id', $this->event->getKey())->get();
        foreach ($templates as $t) {
            $this->templates[] = $t;
        }

        $types = RoleType::get();
        foreach ($types as $rt) {
            $this->roleTypeById["t" . $rt->getKey()] = $rt;
        }

        $this->dateRoles = ["all" => ["sideevents" => [], "roles" => [], "registrations" => []]];
    }

    private function insertRoleAtDate($date, Role $role, Registration $registration)
    {
        if (!isset($this->dateRoles[$date])) {
            $this->dateRoles[$date] = ["sideevents" => [], "roles" => [], "registrations" => []];
        }
        $this->dateRoles[$date]["roles"][] = $role;
        $this->dateRoles[$date]["registrations"][] = $registration;
    }

    private function createRoleForSideevent(SideEvent $sideevent, Registration $registration)
    {
        // if the side event has a competition, accredit the person. Do not
        // accredit for other side events (gala diner, cocktails, etc)
        if ($sideevent->competition?->exists) {
            // in this case, the fencer has no specific role, so is an athlete
            $date = $this->safeDate($sideevent->competition->competition_weapon_check, 'Y-m-d');

            // requirement 6.1.1: For team events, display the team name as well
            // adding the team name causes too much flutter in the Role box. We can add it again
            // if the general layout for accreditations is adjusted

            // create a temporary, unsaved Role object to hold the 'Athlete' role, named after
            // the competition
            $role = new Role();
            $role->role_id = 0;
            $role->role_name = $sideevent->competition->abbreviation();

            $this->insertRoleAtDate($date, $role, $registration);
        }
    }

    private function checkForGlobalRoles()
    {
        // this check is superfluous, as we do not support sideevent specific roles
        // in the front-end anymore. The code is kept for historical purposes,
        // but is not invoked.
        //
        // check to see if some roles are given for all dates anyway
        foreach ($this->rolesById as $k => $r) {
            $foralldates = true;
            foreach ($this->dateRoles as $k => $v) {
                if ($k != "all") {
                    $found = false;
                    foreach ($v["roles"] as $r2) {
                        // only match existing roles, not temporary athlete roles
                        // but those would not/should not exist in rolesById anyway
                        if ($r2->getKey() == $r->getKey() && $r2->exists) {
                            $found = true;
                            break;
                        }
                    }

                    // if the role was not found, it is apparently not set for all the dates
                    if (!$found) {
                        $foralldates = false;
                        break;
                    }
                }
            }

            // this role is set for all dates, so add it to the 'all' list
            if ($foralldates) {
                $this->dateRoles["all"]["roles"][] = $r;

                // filter out the role from the date fields
                foreach ($this->dateRoles as $k => $v) {
                    if ($k != "all") {
                        $this->dateRoles[$k]["roles"] = array_filter($this->dateRoles[$k]["roles"], function ($item) use ($r) {
                            return $item->getKey() != $r->getKey();
                        });
                    }
                }
            }
        }
    }

    public function checkRolesAndDates(Collection $registrations)
    {
        // create a list of dates and the roles this fencer has on each date.
        // the dates are the days of each side event

        // attach sideevents to their starting date
        foreach ($this->sidesById as $k => $s) {
            // we do this seemingly unnecessary stuff to avoid issues with bad date representation
            $date = $this->safeDate($s->starts, 'Y-m-d');
            if (!isset($dates[$date])) {
                $this->dateRoles[$date] = ["sideevents" => [], "roles" => [], "registrations" => []];
            }
            $this->dateRoles[$date]["sideevents"][] = $s;
        }

        // add roles to each sideevent
        foreach ($registrations as $r) {
            $sideevent = $this->sidesById["s" . $r->registration_event] ?? null;
            $role = $this->rolesById["r" . $r->registration_role] ?? null;

            if (!empty($sideevent)) {
                if (empty($role)) {
                    // just a mere participant
                    $this->createRoleForSideevent($sideevent, $r);
                }
                else {
                    // this is something we do not support in the front-end anymore: roles for specific
                    // events (coach at WS1 for example)
                    $date = $this->safeDate($sideevent->starts, 'Y-m-d');
                    $this->insertRoleAtDate($date, $role, $r);
                }
            }
            else if (!empty($role)) {
                // event-wide role, which is the 'typical' case
                $this->insertRoleAtDate("all", $role, $r);
            }
        }
        // unnecessary check commented out
        //$this->checkForGlobalRoles();
        return $this->dateRoles; // return for testing purposes
    }

    // given the role-ids for which this template is assigned, find all the roles
    // assigned to various dates for this user
    private function findAssignedRoles(array $roleids)
    {
        $roles = [];
        $alreadyfound = [];
        foreach ($this->dateRoles as $dt => $datespec) {
            foreach ($datespec["roles"] as $role) {
                // add roles that are in the list of roles of this template
                // if role=0 is in the template, add all the roles (which are
                // individual competition events)
                if (
                       in_array(strval($role->getKey()), $roleids)
                    && (!in_array($role->getKey(), $alreadyfound) || $role->getKey() == 0) // add role.id=0 duplicates
                ) {
                    $roles[] = $role;
                    $alreadyfound[] = $role->getKey(); // make sure there are no duplicates
                }
            }
        }
        return $roles;
    }

    private function createAccreditationForTemplate(AccreditationTemplate $template)
    {
        $content = json_decode($template->content, true);
        $roleids = isset($content["roles"]) ? $content["roles"] : array();
        $assignedRoles = $this->findAssignedRoles($roleids);

        // if any of the roles for this template was assigned, create an accreditation
        if (count($assignedRoles) > 0) {
            // see if any of the template roles appears in the ALL list. In that case, we
            // assign all the roles managed by this accreditation for ALL dates
            $foundall = false;
            foreach ($this->dateRoles["all"]["roles"] as $role) {
                // if the template role appears in the all list, it must appear in the assignedRoles as well
                if (in_array(strval($role->getKey()), $roleids)) {
                    return $this->createTemplate($template, $assignedRoles, ["ALL"]);
                }
            }

            // no template role found for all the dates, so this is an athlete
            // loop over all dates, find all assigned roles for each date
            $founddates = array();
            foreach ($this->dateRoles as $dt => $spec) {
                if ($dt != "all") {
                    foreach ($spec["roles"] as $rl) {
                        if (in_array(strval($rl->getKey()), $roleids)) {
                            $founddates[] = $dt;
                            break;
                        }
                    }
                }
            }

            // if we find a role in each of the dates, assign roles for all dates anyway
            // (compare with sizeof()-1 because the 'all' entry is included in $dates)
            if (count($founddates) >= (count(array_keys($this->dateRoles)) - 1)) {
                return $this->createTemplate($template, $assignedRoles, array("ALL"));
            }
            else {
                return $this->createTemplate($template, $assignedRoles, $founddates);
            }
        }
        return null;
    }

    private function createTemplate(AccreditationTemplate $template, array $assignedRoles, array $dates)
    {
        $yob = $this->safeDate($this->fencer->fencer_dob, 'Y');
        $catnum = Category::categoryFromYear($yob, $this->event->event_open);
        $accr = array(
            "category" => $catnum,
            "organisation" => "",
            "roles" => array(),
            "dates" => array(),
            "lastname" => strtoupper($this->fencer->fencer_surname),
            "firstname" => $this->fencer->fencer_firstname
        );

        // Find the country belonging to the roles for this template and these dates
        // The country is associated with the registration and not with the fencer,
        // so we can have a Finnish support member under the wings of a French Head of Delegation
        // The country in this case indicates foremost which Head of Delegation is responsible.
        $countries = [];
        $assignedRoleIds = collect($assignedRoles)->map(fn (Role $role) => $role->getKey())->toArray();
        foreach ($this->dateRoles as $date => $structure) {
            // if we generate for ALL dates, check registrations for each date
            if (in_array("ALL", $dates) || in_array($date, $dates)) {
                foreach ($structure['registrations'] as $registration) {
                    if (
                           (!empty($registration->registration_role) && in_array($registration->registration_role, $assignedRoleIds))
                        || (empty($registration->registration_role) && in_array(0, $assignedRoleIds))
                    ) {
                        $ckey = 'c' . ($registration->registration_country ?? 0);
                        if (!isset($countries[$ckey])) {
                            $countries[$ckey] = 0;
                        }
                        $countries[$ckey] += 1;
                    }
                }
            }
        }

        // take a sensible default
        $country = Country::find($this->fencer->fencer_country);
        $ckeys = array_keys($countries);
        if (count($ckeys) == 1) {
            if ($ckeys[0] != 'c0') {
                $country2 = Country::find(intval(substr($ckeys[0], 1)));
                if (!empty($country2)) {
                    $country = $country2;
                }
            }
            // else this is organisation/official, the template will deal with it
            // just keep the fencer country setting for now
        }
        else if (count($ckeys) > 1) {
            // multiple countries, this should not happen
            \Log::error('Found accreditation for multiple countries ' . json_encode([$countries, $this->fencer->getKey(), $this->event->getKey()]));

            // if the fencer country is in the list, take that anyway
            if (!in_array('c' . $this->fencer->fencer_country, $ckeys)) {
                // else sort the array, take the country occuring most often
                // this would be the HoD that has the most to say
                // First remove the 'c0' entry for non-existing roles
                // If only one entry remains now, it will be sorted at the top anyway
                if (isset($countries['c0'])) unset($countries['c0']);

                // sort, keeping index-relationship
                asort($countries, SORT_NUMERIC);
                $ckeys = array_keys($countries);
                $lastkey = $ckeys[count($ckeys) - 1];
                $country2 = Country::find(intval(substr($lastkey, 1)));
                if (!empty($country2)) {
                    $country = $country2;
                }
            }
        }

        if (!empty($country)) {
            $accr["country"] = $country->country_abbr;
            $accr["country_flag"] = $country->country_flag_path;
        }

        // make sure we change the accreditation when the fencer photo ID changes
        $path = $this->fencer->image();
        if (file_exists($path)) {
            $accr["photo_hash"] = hash_file("sha256", $path);
        }
        else {
            // add a value to indicate the file is non-existant
            $accr["photo_hash"] = "---";
        }

        // make sure we change the accreditation if the template configuration changes
        // we hash the template content JSON string, assuming if it changes, it's content
        // will have changed as well
        $accr["template_hash"] = hash('sha256', $template->content, false);

        // convert dates to a 'SAT 1' kind of display
        foreach ($dates as $k) {
            if ($k != "ALL") {
                $format = $this->safeDate($k, 'j D');
                $entry = str_replace('  ', ' ', strtoupper($format));
                $accr["dates"][] = $entry;
            }
            else {
                $accr["dates"][] = $k;
            }
        }

        // convert all roles to their role name
        foreach ($assignedRoles as $role) {
            $accr["roles"][] = $role->role_name;

            // depending on the role types, set the organisation
            $rtid = $role->role_type;

            if (isset($this->roleTypeById["t" . $rtid])) {
                $orgdecl = $this->roleTypeById["t" . $rtid]->org_declaration;

                // ideally templates should be different for federative and non-federative roles
                // but in case they are not, make sure the organisation is not downgraded
                if ($orgdecl == "Country" && strlen($accr["organisation"]) == 0) {
                    $accr["organisation"] = $accr["country"];
                }
                // do not override 'backward' if someone has an EVF and a ORG role
                else if ($orgdecl == "Org" && $accr["organisation"] != "EVF") {
                    $accr["organisation"] = "ORG";
                }
                else if ($orgdecl == "EVF") {
                    $accr["organisation"] = "EVF";
                }
            }
        }
        // sort the roles to be consistent
        sort($accr["roles"]);

        return ["template" => $template, "content" => $accr];
    }

    private function safeDate($date, $outFormat, $inFormat = 'Y-m-d')
    {
        $date = DateTimeImmutable::createFromFormat($inFormat, $date);
        if ($date === false) {
            return '';
        }
        return $date->format($outFormat);
    }
}
