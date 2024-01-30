<?php

namespace App\Support\Services;

use App\Models\Event;
use App\Models\SideEvent;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Country;
use App\Models\Document;
use App\Models\Fencer;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder;

class AccreditationOverviewService
{
    public $event = null;
    public $sideEventIds = [];
    public $roleById = [];
    public $roleByType = [];
    public $templatesByRole = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    // create an overview of total registrations, total accreditations, dirty accreditations and
    // generated accreditations per:
    // - event
    // - country
    // - role
    // - accreditation template
    public function create()
    {
        $this->initialise();

        $overviewForEvents = $this->createOverviewForEvents();
        $overviewForCountries = $this->createOverviewForCountries();
        $overviewForRoles = $this->createOverviewForRoles();
        $overviewForTemplates = $this->createOverviewForTemplates();

        return array_merge($overviewForEvents, $overviewForCountries, $overviewForRoles, $overviewForTemplates);
    }

    public function initialise()
    {
        $this->listOfSideEventsWithCompetition();
        $this->createRoleStructures();
        $this->templatesByRole = AccreditationTemplate::byRoleId($this->event);
    }

    private function listOfSideEventsWithCompetition()
    {
        $this->sideEventIds = [];
        foreach ($this->event->sides()->with('competition')->get() as $sideEvent) {
            if (!empty($sideEvent->competition)) {
                $this->sideEventIds[] = $sideEvent->getKey();
            }
        }
    }

    private function createRoleStructures()
    {
        $roles = Role::get();
        $this->roleById = [];
        $this->roleByType = [];
        foreach ($roles as $r) {
            $this->roleById["r" . $r->role_id] = $r;
            if (!isset($this->roleByType["r" . $r->role_type])) {
                $this->roleByType["r" . $r->role_type] = [];
            }
            $this->roleByType["r" . $r->role_type][] = $r->role_id;
        }
    }

    public function humanFilesize($bytes, $decimals = 1)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    private function findDocuments(string $type, int $modelId)
    {
        return $this->event->documents()->where('type', $type)->where('type_id', $modelId)->get();
    }

    private function decorateResults($type, $namePart, $results, $removeEmptyLines = true)
    {
        // parse the results into a list of AccreditationOverview lines
        $retval = [];
        foreach ($results as $obj) {
            $total = intval($obj->registrations) + intval($obj->accreditations) + intval($obj->dirty) + intval($obj->generated);
            $documents = $this->findDocuments($namePart, $obj->id)->map(function ($d) {
                return [
                    "id" => $d->getKey(),
                    "size" =>  $d->fileExists() ? $this->humanFilesize(filesize($d->getPath())) : '-',
                    "available" => $d->fileExists() ? 'Y' : 'N'
                ];
            });

            // filter out empty lines
            if (!$removeEmptyLines || count($documents) > 0 || $total > 0) {
                $retval[] = array(
                    $type,
                    $obj->id,
                    [intval($obj->registrations), intval($obj->accreditations), intval($obj->dirty), intval($obj->generated)],
                    $documents->toArray()
                );
            }
        }
        return $retval;
    }

    public function createOverviewForEvents()
    {
        // for side-events, we only display the athletes and participants
        // we do that by selecting on the accreditation templates
        $k1 = "r0"; // athlete role
        $acceptableTemplates = [-1];
        if (isset($this->templatesByRole[$k1])) {
            $acceptableTemplates = $this->templatesByRole[$k1];
        }
        $acceptableRoles = [0];

        $st = SideEvent::tableName();
        $rt = Registration::tableName();
        $at = Accreditation::tableName();

        $registrationClause = DB::table($rt)
            ->select(DB::Raw('registration_event, count(*) as total'))
            ->where('registration_mainevent', $this->event->getKey())
            ->whereIn('registration_role', $acceptableRoles)
            ->groupBy('registration_event');

        $accreditationClause = DB::table($rt)
            ->select(DB::Raw('registration_event, count(*) as total'))
            ->join($at . ' AS ar', 'ar.fencer_id', '=', $rt . '.registration_fencer')
            ->where('registration_mainevent', $this->event->getKey())
            ->where('ar.event_id', '=', DB::Raw($rt . '.registration_mainevent'))
            ->whereIn('ar.template_id', $acceptableTemplates)
            ->groupBy('registration_event');

        $dirtyClause = DB::table($rt)
            ->select(DB::Raw('registration_event, count(*) as total'))
            ->join($at . ' AS ar', 'ar.fencer_id', '=', $rt . '.registration_fencer')
            ->where('registration_mainevent', $this->event->getKey())
            ->where('ar.event_id', '=', DB::Raw($rt . '.registration_mainevent'))
            ->whereIn('ar.template_id', $acceptableTemplates)
            ->where('ar.is_dirty', '<>', null)
            ->groupBy('registration_event');

        $cleanClause = DB::table($rt)
            ->select(DB::Raw('registration_event, count(*) as total'))
            ->join($at . ' AS ar', 'ar.fencer_id', '=', $rt . '.registration_fencer')
            ->where('registration_mainevent', $this->event->getKey())
            ->where('ar.event_id', '=', DB::Raw($rt . '.registration_mainevent'))
            ->whereIn('ar.template_id', $acceptableTemplates)
            ->where('ar.is_dirty', '=', null)
            ->groupBy('registration_event');

        $results = SideEvent::select(
            $st . '.id',
            $st . '.title',
            "r.total as registrations",
            "a.total as accreditations",
            "d.total as dirty",
            "g.total as generated"
        )
            ->leftJoinSub($registrationClause, 'r', function (JoinClause $join) use ($st) {
                $join->on($st . '.id', '=', 'r.registration_event');
            })
            ->leftJoinSub($accreditationClause, 'a', function (JoinClause $join) use ($st) {
                $join->on($st . '.id', '=', 'a.registration_event');
            })
            ->leftJoinSub($dirtyClause, 'd', function (JoinClause $join) use ($st) {
                $join->on($st . '.id', '=', 'd.registration_event');
            })
            ->leftJoinSub($cleanClause, 'g', function (JoinClause $join) use ($st) {
                $join->on($st . '.id', '=', 'g.registration_event');
            })
            ->where($st . ".competition_id", "<>", null)
            ->where($st . ".event_id", $this->event->getKey())
            ->orderBy($st . ".title")
            ->get();

        return $this->decorateResults('E', 'Event', $results);
    }

    public function createOverviewForCountries()
    {
        // for countries, we only display the athletes and federative roles
        // we do that by selecting on the accreditation templates
        $k1 = "r0"; // athlete role
        $k2 = "r" . RoleType::COUNTRY; // federative role
        $acceptableTemplates = array_merge($this->templatesByRole[$k1] ?? [], $this->templatesByRole[$k2] ?? []);
        if (empty($acceptableTemplates)) {
            $acceptableTemplates = [-1];
        }
        $acceptableRoles = array_merge([0], $this->roleByType[$k2]);

        $at = Accreditation::tableName();
        $ft = Fencer::tableName();
        $rt = Registration::tableName();
        $ct = Country::tableName();

        $registrationClause = DB::table($rt)
            ->select(DB::Raw('fr.fencer_country, count(*) as total'))
            ->join($ft . ' AS fr', 'fr.fencer_id', '=', $rt . '.registration_fencer')
            ->where('registration_mainevent', $this->event->getKey())
            ->whereIn('registration_role', $acceptableRoles)
            ->where(function (Builder $query) {
                $query->whereIn('registration_event', $this->sideEventIds)
                    ->orWhere('registration_event', '=', null);
            })
            ->groupBy('fr.fencer_country');

        $accreditationClause = DB::table($at)
            ->select(DB::Raw('fa.fencer_country, count(*) as total'))
            ->join($ft . ' AS fa', 'fa.fencer_id', '=', $at . '.fencer_id')
            ->where($at . '.event_id', $this->event->getKey())
            ->whereIn($at . '.template_id', $acceptableTemplates)
            ->groupBy('fa.fencer_country');

        $dirtyClause = DB::table($at)
            ->select(DB::Raw('fb.fencer_country, count(*) as total'))
            ->join($ft . ' AS fb', 'fb.fencer_id', '=', $at . '.fencer_id')
            ->where($at . '.event_id', $this->event->getKey())
            ->whereIn($at . '.template_id', $acceptableTemplates)
            ->where($at . '.is_dirty', '<>', null)
            ->groupBy('fb.fencer_country');

        $cleanClause = DB::table($at)
            ->select(DB::Raw('fc.fencer_country, count(*) as total'))
            ->join($ft . ' AS fc', 'fc.fencer_id', '=', $at . '.fencer_id')
            ->where($at . '.event_id', $this->event->getKey())
            ->whereIn($at . '.template_id', $acceptableTemplates)
            ->where($at . '.is_dirty', '=', null)
            ->groupBy('fc.fencer_country');

        $results = Country::select(
            $ct . '.country_id AS id',
            "r.total as registrations",
            "a.total as accreditations",
            "d.total as dirty",
            "g.total as generated"
        )
            ->leftJoinSub($registrationClause, 'r', function (JoinClause $join) use ($ct) {
                $join->on($ct . '.country_id', '=', 'r.fencer_country');
            })
            ->leftJoinSub($accreditationClause, 'a', function (JoinClause $join) use ($ct) {
                $join->on($ct . '.country_id', '=', 'a.fencer_country');
            })
            ->leftJoinSub($dirtyClause, 'd', function (JoinClause $join) use ($ct) {
                $join->on($ct . '.country_id', '=', 'd.fencer_country');
            })
            ->leftJoinSub($cleanClause, 'g', function (JoinClause $join) use ($ct) {
                $join->on($ct . '.country_id', '=', 'g.fencer_country');
            })
            ->get();
        return $this->decorateResults('C', 'Country', $results);
    }

    public function createOverviewForRoles()
    {
        $at = Accreditation::tableName();
        $ft = Fencer::tableName();
        $rt = Registration::tableName();
        $ct = Country::tableName();

        $registrationClause = DB::table($rt)
            ->select(DB::Raw('registration_role, count(*) as total'))
            ->where('registration_mainevent', $this->event->getKey())
            ->where('registration_role', '>', 0)
            ->where('registration_event', '=', null)
            // the following is needed if we want to list all athlete roles as well
            // a similar clause has to be added to the other subqueries instead of
            // the 'is null' above
            //->where(function (Builder $query) {
            //    $query->whereIn('registration_event', $this->sideEventIds)
            //        ->orWhere('registration_event', '=', null);
            //})
            ->groupBy('registration_role');

        // in the subclauses, we use '1 as cntX' because each role will related to
        // exactly 1 accreditation. If we use count(*), we are going to count all
        // accreditations for the selected fencers, but that will include accreditations
        // for unrelated roles.
        $accreditationClause1 = DB::table($at)
            ->select('r1.registration_role', 'r1.registration_fencer', DB::Raw('1 as cnt1'))
            ->join($rt . ' AS r1', function (JoinClause $join) use ($at) {
                $join->on('r1.registration_fencer', '=', $at . '.fencer_id')
                    ->on('r1.registration_mainevent', '=', $at . '.event_id');
            })
            ->where($at . '.event_id', $this->event->getKey())
            ->where('r1.registration_role', '>', 0)
            ->where('r1.registration_event', '=', null)
            ->groupBy('r1.registration_role', 'r1.registration_fencer');
        $accreditationClause = DB::query()
            ->fromSub($accreditationClause1, "s1")
            ->select(DB::Raw("s1.registration_role, sum(s1.cnt1) as total"))
            ->groupBy("s1.registration_role");

        $dirtyClause1 = DB::table($at)
            ->select('r2.registration_role', 'r2.registration_fencer', DB::Raw('1 as cnt2'))
            ->join($rt . ' AS r2', function (JoinClause $join) use ($at) {
                $join->on('r2.registration_fencer', '=', $at . '.fencer_id')
                    ->on('r2.registration_mainevent', '=', $at . '.event_id');
            })
            ->where('r2.registration_role', '>', 0)
            ->where($at . '.event_id', $this->event->getKey())
            ->where($at . ".is_dirty", "<>", null)
            ->where('r2.registration_event', '=', null)
            ->groupBy('r2.registration_role', 'r2.registration_fencer');
        $dirtyClause = DB::query()
            ->fromSub($dirtyClause1, "s2")
            ->select(DB::Raw("s2.registration_role, sum(s2.cnt2) as total"))
            ->groupBy("s2.registration_role");

        $cleanClause1 = DB::table($at)
            ->select('r3.registration_role', 'r3.registration_fencer', DB::Raw('1 as cnt3'))
            ->join($rt . ' AS r3', function (JoinClause $join) use ($at) {
                $join->on('r3.registration_fencer', '=', $at . '.fencer_id')
                    ->on('r3.registration_mainevent', '=', $at . '.event_id');
            })
            ->where($at . '.event_id', $this->event->getKey())
            ->where('r3.registration_role', '>', 0)
            ->where($at . ".is_dirty", "=", null)
            ->where('r3.registration_event', '=', null)
            ->groupBy('r3.registration_role', 'r3.registration_fencer');
        $cleanClause = DB::query()
            ->fromSub($cleanClause1, "s3")
            ->select(DB::Raw("s3.registration_role, sum(s3.cnt3) as total"))
            ->groupBy("s3.registration_role");
            
        $results = Registration::select(
            $rt . '.registration_role AS id',
            DB::Raw("max(r.total) as registrations"),
            DB::Raw("max(a.total) as accreditations"),
            DB::Raw("max(d.total) as dirty"),
            DB::Raw("max(g.total) as `generated`")
        )
            ->leftJoinSub($registrationClause, 'r', function (JoinClause $join) use ($rt) {
                $join->on($rt . '.registration_role', '=', 'r.registration_role');
            })
            ->leftJoinSub($accreditationClause, 'a', function (JoinClause $join) use ($rt) {
                $join->on($rt . '.registration_role', '=', 'a.registration_role');
            })
            ->leftJoinSub($dirtyClause, 'd', function (JoinClause $join) use ($rt) {
                $join->on($rt . '.registration_role', '=', 'd.registration_role');
            })
            ->leftJoinSub($cleanClause, 'g', function (JoinClause $join) use ($rt) {
                $join->on($rt . '.registration_role', '=', 'g.registration_role');
            })
            ->where($rt . '.registration_mainevent', $this->event->getKey()) // only look at registrations from this event
            ->where($rt . '.registration_role', '>', 0) // no athlete roles
            ->groupBy($rt . '.registration_role') // squash all to a distinct list of roles
            ->get();

        return $this->decorateResults('R', 'Role', $results);
    }

    public function createOverviewForTemplates()
    {
        $acceptableKeys = collect(array_keys($this->templatesByRole))->filter(fn ($key) => $key != 'r0');
        $acceptableTemplates = [];
        foreach ($this->templatesByRole as $key => $templates) {
            if ($acceptableKeys->contains($key)) {
                $acceptableTemplates = array_merge($acceptableTemplates, $this->templatesByRole[$key]);
            }
        }

        $tt = AccreditationTemplate::tableName();
        $at = Accreditation::tableName();
        $rt = Registration::tableName();

        $registrationClause = DB::table($at)
            ->select(DB::Raw($at . '.template_id, count(*) as total'))
            ->join($rt . ' AS r', $at . '.fencer_id', '=', 'r.registration_fencer')
            ->where($at . '.event_id', $this->event->getKey())
            ->where('r.registration_mainevent', '=', DB::Raw($at . '.event_id'))
            ->where(function (Builder $query) {
                $query->whereIn('r.registration_event', $this->sideEventIds)
                    ->orWhere('r.registration_event', '=', null);
            })
            ->groupBy($at . '.template_id');

        $accreditationClause = DB::table($at)
            ->select(DB::Raw($at . '.template_id, count(*) as total'))
            ->where($at . '.event_id', $this->event->getKey())
            ->groupBy($at . '.template_id');

        $dirtyClause = DB::table($at)
            ->select(DB::Raw($at . '.template_id, count(*) as total'))
            ->where($at . '.event_id', $this->event->getKey())
            ->where($at . '.is_dirty', '<>', null)
            ->groupBy($at . '.template_id');

        $cleanClause = DB::table($at)
            ->select(DB::Raw($at . '.template_id, count(*) as total'))
            ->where($at . '.event_id', $this->event->getKey())
            ->where($at . '.is_dirty', '=', null)
            ->groupBy($at . '.template_id');

        $results = AccreditationTemplate::select(
            $tt . ".id",
            $tt . ".name",
            "r.total as registrations",
            "a.total as accreditations",
            "d.total as dirty",
            "g.total as generated"
        )
            ->leftJoinSub($registrationClause, 'r', function (JoinClause $join) use ($tt) {
                $join->on($tt . '.id', '=', 'r.template_id');
            })
            ->leftJoinSub($accreditationClause, 'a', function (JoinClause $join) use ($tt) {
                $join->on($tt . '.id', '=', 'a.template_id');
            })
            ->leftJoinSub($dirtyClause, 'd', function (JoinClause $join) use ($tt) {
                $join->on($tt . '.id', '=', 'd.template_id');
            })
            ->leftJoinSub($cleanClause, 'g', function (JoinClause $join) use ($tt) {
                $join->on($tt . '.id', '=', 'g.template_id');
            })
            ->where($tt . '.event_id', $this->event->getKey())
            ->whereIn($tt . '.id', $acceptableTemplates)
            ->get();

        return $this->decorateResults('T', 'Template', $results, false);
    }
}
