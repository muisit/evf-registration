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

class RegenerateBadges extends Job
{
    public $event = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Event $event)
    {
        $this->event = $event->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::debug("RegenerateBadges job");
        if ($this->event->exists) {
            \Log::debug("event exists, making all existing accreditations dirty");
            // make all existing accreditations for this event dirty
            // This is a catch all to make sure we get all accreditations
            Accreditation::where('event_id', $this->event->getKey())->update(['is_dirty' => date('Y-m-d H:i:s')]);

            $this->makeAllRegistrationsDirty();
        }
        else {
            \Log::debug("event does not exist");
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
