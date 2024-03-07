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
use Carbon\Carbon;

// We check for dirty accreditations in a queue job, so it is naturally placed directly after
// any regeneration attempts
class CheckDirtyBadges extends Job implements ShouldBeUniqueUntilProcessing
{
    private $immediate = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($immediate = false)
    {
        $this->immediate = $immediate;
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
        $events = Event::where('event_open', '>', Carbon::now()->toDateString())->get();
        foreach ($events as $event) {
            if ($event->allowGenerationOfAccreditations()) {
                // make sure all registered fencers have accreditations
                $this->makeAllRegistrationsDirty($event);
                $this->checkDirtyAccreditations($event);
            }
        }
    }

    private function checkDirtyAccreditations(Event $event)
    {
        // only look at accreditations that were made dirty over 10 minutes ago, to avoid creating accreditations
        // when the fencer and registration data is still being updated
        $notafter = date('Y-m-d H:i:s', $this->immediate ? time() : time() - 10 * 60);
        $accreditations = DB::table(Accreditation::tableName())
            ->select('fencer_id')
            ->where('is_dirty', '<>', null)
            ->where('is_dirty', '<=', $notafter)
            ->where('event_id', $event->getKey())
            ->groupBy('fencer_id')
            ->get();

        foreach ($accreditations as $row) {
            $job = new CheckBadge($row->fencer_id, $event->getKey());
            dispatch($job);
        }

        // also get all accreditations that were generated, but that do not have a
        // file, a generation time, but also not an is_dirty value
        // These accreditations were created while the application was set to 'do not generate'
        $accreditations = DB::table(Accreditation::tableName())
            ->select('fencer_id')
            ->where('is_dirty', null)
            ->where('generated', null)
            ->where('file_id', null)
            ->where('event_id', $event->getKey())
            ->groupBy('fencer_id')
            ->get();

        foreach ($accreditations as $row) {
            $job = new CheckBadge($row->fencer_id, $event->getKey());
            dispatch($job);
        }
    }

    private function makeAllRegistrationsDirty(Event $event)
    {
        // loop over all different fencers that are registered and make accreditations dirty.
        // Only select fencers that have no accreditations, so we can make new ones.
        $fids = Registration::select(DB::Raw('distinct registration_fencer'))
            ->where('registration_mainevent', $event->getKey())
            ->whereNot(function (EBuilder $query) {
                $query->whereExists(Accreditation::select('*')->where('fencer_id', DB::Raw(Registration::tableName() . '.registration_fencer')));
            })
            ->get()->pluck('registration_fencer');

        $template = AccreditationTemplate::where('event_id', $event->getKey())->where('is_default', 'N')->first();
        if (!empty($template)) {
            foreach ($fids as $fid) {
                $this->makeDirty($fid, $event, $template);
            }
        }
    }

    private function makeDirty(int $fid, Event $event, AccreditationTemplate $template)
    {
        $cnt = Accreditation::where('fencer_id', $fid)->where('event_id', $event->getKey())->count();
        if ($cnt == 0) {
            // we create an empty accreditation to signal the queue that this set needs to be reevaluated
            $dt = new Accreditation();
            $dt->fencer_id = $fid;
            $dt->event_id = $event->getKey();
            $dt->data = json_encode([]);
            $dt->template_id = $template->getKey();
            $dt->file_id = null;
            $dt->generated = null;
            $dt->is_dirty = date('Y-m-d H:i:s');
            $dt->save();
        }
        else {
            Accreditation::where('fencer_id', $fid)->where("event_id", $event->getKey())->update(['is_dirty' => date('Y-m-d H:i:s')]);
        }
    }
}
