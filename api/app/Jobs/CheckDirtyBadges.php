<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder as QBuilder;
use Illuminate\Database\Eloquent\Builder as EBuilder;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// We check for dirty accreditations in a queue job, so it is naturally placed directly after
// any regeneration attempts
class CheckDirtyBadges extends Job implements ShouldBeUniqueUntilProcessing
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function uniqueId(): string
    {
        return "globally_unique";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::debug("handling CheckDirtyBadges");
        // only look at accreditations that were made dirty over 10 minutes ago, to avoid creating accreditations
        // when the fencer and registration data is still being updated
        $notafter = date('Y-m-d H:i:s', time() - 10 * 60);
        $accreditations = DB::table(Accreditation::tableName())
            ->select('fencer_id', 'event_id')
            ->where('is_dirty', '<>', null)
            ->where('is_dirty', '<', $notafter)
            ->groupBy('fencer_id', 'event_id')
            ->orderBy('fencer_id', 'asc')->orderBy('event_id', 'asc')
            ->get();

        $eventsById = [];

        foreach ($accreditations as $row) {
            $eventId = $row->event_id;
            if (!isset($eventsById['e' . $eventId])) {
                $event = Event::find($eventId);
                if (!empty($event)) {
                    $eventsById['e' . $event->getKey()] = $event;
                }
            }

            $event = $eventsById['e' . $eventId];
            \Log::debug("checking " . ($event->allowGenerationOfAccreditations() ? 'allowed' : 'not allowed'));
            if (!empty($event) && $event->allowGenerationOfAccreditations()) {
                \Log::debug("dispatching checkbadge job");
                $job = new CheckBadge($row->fencer_id, $row->event_id);
                dispatch($job);
            }
        }
    }

    private function makeAllRegistrationsDirty()
    {
        // loop over all different fencers that are registered and make accreditations dirty.
        // Only select fencers that have no accreditations, so we can make new ones.
        $fids = Registration::select(DB::Raw('distinct registration_fencer'))
            ->where('registration_mainevent', $this->event->getKey())
            ->whereNot(function (EBuilder $query) {
                $queryBuilder = Accreditation::select('*')->where('fencer_id', DB::Raw(Registration::tableName() . '.registration_fencer'))->toBase();
                $query->whereExists(Accreditation::select('*')->where('fencer_id', DB::Raw(Registration::tableName() . '.registration_fencer')));
            })
            ->get()->pluck('registration_fencer');

        \Log::debug("making new accreditations for " . $fids->count() . ' fencers');
        $template = AccreditationTemplate::where('event_id', $this->event->getKey())->first();
        if (!empty($template)) {
            foreach ($fids as $fid) {
                $this->makeDirty($fid, $template);
            }
        }
    }

    private function makeDirty(int $fid, AccreditationTemplate $template)
    {
        $cnt = Accreditation::where('fencer_id', $fid)->where('event_id', $this->event->getKey())->count();
        if ($cnt == 0) {
            \Log::debug("creating new accreditation based on a template");
            // we create an empty accreditation to signal the queue that this set needs to be reevaluated
            $dt = new Accreditation();
            $dt->fencer_id = $fid;
            $dt->event_id = $this->event->getKey();
            $dt->data = json_encode([]);
            $dt->template_id = $template->getKey();
            $dt->file_id = null;
            $dt->generated = null;
            $dt->is_dirty = date('Y-m-d H:i:s');
            $dt->save();
        }
        else {
            \Log::debug("accreditation exists anyway, so making it dirty instead");
            Accreditation::where('fencer_id', $fid)->where("event_id", $this->event->getKey())->update(['is_dirty' => date('Y-m-d H:i:s')]);
        }
    }
}
