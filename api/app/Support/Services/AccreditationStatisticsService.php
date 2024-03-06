<?php

namespace App\Support\Services;

use App\Models\Accreditation;
use App\Models\AccreditationDocument;
use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\SideEvent;
use App\Models\Registration;
use App\Models\Schemas\AccreditationStatistics;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AccreditationStatisticsService
{
    private Event $event;
    private AccreditationTemplate $athleteTemplate;

    public function __construct(Event $e)
    {
        $this->event = $e;
        $this->athleteTemplate = $this->findAthleteTemplate();
    }

    public function generate()
    {
        $retval = [];
        $sideevents = $this->event->sides;
        foreach ($sideevents as $event) {
            $key = 's' . $event->getKey();
            $retval[$key] = new AccreditationStatistics();
            $retval[$key]->eventId = $event->getKey();
        }

        $retval = $this->countAccreditations($retval);
        $retval = $this->countRegistrations($retval);
        $retval = $this->countDocuments($retval);
        return array_values($retval);
    }

    private function countDocuments(array $byevent)
    {
        $ad = AccreditationDocument::tableName();
        $rt = Registration::tableName();
        $at = Accreditation::tableName();
        $rows = DB::table(Registration::tableName())
            ->select(['registration_event', 'ad.status', DB::Raw("count(*) as cnt")])
            ->join($at . ' AS ar', 'ar.fencer_id', '=', $rt . '.registration_fencer')
            ->join($ad . ' AS ad', 'ad.accreditation_id', '=', 'ar.id')
            ->where("registration_mainevent", $this->event->getKey())
            ->groupBy('registration_event')
            ->groupBy('ad.status')
            ->get();

        foreach ($rows as $row) {
            $key = 's' . $row->registration_event;
            if (isset($byevent[$key])) {
                $status = $row->status;
                switch ($status) {
                    default:
                        $byevent[$key]->checkin += intval($row->cnt);
                        break;
                    case AccreditationDocument::STATUS_CHECKOUT:
                        $byevent[$key]->checkout += intval($row->cnt);
                        break;
                }
            }
        }
        return $byevent;
    }

    private function countRegistrations(array $byevent)
    {
        $rows = DB::table(Registration::tableName())
            ->select(['registration_event', 'registration_state', DB::Raw("count(*) as cnt")])
            ->where("registration_mainevent", $this->event->getKey())
            ->groupBy('registration_event')
            ->groupBy('registration_state')
            ->get();

        foreach ($rows as $row) {
            $key = 's' . $row->registration_event;
            if (isset($byevent[$key])) {
                $status = $row->registration_state;
                switch ($status) {
                    case Registration::STATE_PRESENT:
                        $byevent[$key]->present = intval($row->cnt);
                        break;
                    case Registration::STATE_CANCELLED:
                        $byevent[$key]->cancelled = intval($row->cnt);
                        break;
                    case Registration::STATE_REGISTERED:
                    default:
                        $byevent[$key]->pending = intval($row->cnt);
                        break;
                }
            }
        }
        return $byevent;
    }

    private function findAthleteTemplate()
    {
        $templates = AccreditationTemplate::where('event_id', $this->event->getKey())->get();
        foreach ($templates as $template) {
            $roles = $template->forRoles();
            \Log::debug("testing template for " . json_encode($roles));
            if (in_array('0', $roles)) {
                \Log::debug("template found");
                return $template;
            }
        }
        return null;
    }

    private function countAccreditations(array $byevent)
    {
        if (empty($this->athleteTemplate)) {
            \Log::debug("empty template, no accreditation count");
            return $byevent;
        }

        $st = SideEvent::tableName();
        $rt = Registration::tableName();
        $at = Accreditation::tableName();

        $registrationClause = DB::table($rt)
            ->select(DB::Raw('registration_event, count(*) as total'))
            ->where('registration_mainevent', $this->event->getKey())
            ->where('registration_role', '0')
            ->groupBy('registration_event');

        $accreditationClause = DB::table($rt)
            ->select(DB::Raw('registration_event, count(*) as total'))
            ->join($at . ' AS ar', 'ar.fencer_id', '=', $rt . '.registration_fencer')
            ->where('registration_mainevent', $this->event->getKey())
            ->where('ar.event_id', '=', DB::Raw($rt . '.registration_mainevent'))
            ->where('ar.template_id', $this->athleteTemplate->getKey())
            ->groupBy('registration_event');

        $results = SideEvent::select(
            $st . '.id',
            "r.total as registrations",
            "a.total as accreditations",
        )
            ->leftJoinSub($registrationClause, 'r', function (JoinClause $join) use ($st) {
                $join->on($st . '.id', '=', 'r.registration_event');
            })
            ->leftJoinSub($accreditationClause, 'a', function (JoinClause $join) use ($st) {
                $join->on($st . '.id', '=', 'a.registration_event');
            })
            ->where($st . '.event_id', $this->event->getKey())
            ->get();
        \Log::debug("registration listing, found " . count($results));
        foreach ($results as $row) {
            $key = 's' . $row->id;
            \Log::debug("key is $key, row is " . json_encode($row));
            if (isset($byevent[$key])) {
                \Log::debug("setting values " . $row->registrations . '/' . $row->accreditations);
                $byevent[$key]->registrations = intval($row->registrations);
                $byevent[$key]->accreditations = intval($row->accreditations);
            }
        }
        return $byevent;
    }
}
