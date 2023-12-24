<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Fencer;
use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Registration;
use App\Support\Services\AccreditationCreateService;
use App\Support\Services\AccreditationMatchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\Builder as QBuilder;
use Illuminate\Database\Eloquent\Builder as EBuilder;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// This job checks to see if all available accreditations for a fencer are still
// matching as far as data is concerned. It will remove accreditations that are
// no longer required, add new accreditations if required and create appropiate
// CreateBadge jobs for accreditations that have changed or have been added.
//
// When accreditations are removed, the accompanying files are removed as well
//
class CheckBadge extends Job implements ShouldBeUniqueUntilProcessing
{
    public int $fencerId;
    public int $eventId;

    private $executeSynchronously = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $fencerId, int $eventId)
    {
        $this->fencerId = $fencerId;
        $this->eventId = $eventId;
    }

    public function uniqueId(): string
    {
        return "check" . $this->fencerId . "_" . $this->eventId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        return $this->doHandle();
    }

    public function handleSynchronous()
    {
        $this->executeSynchronously = true;
        return $this->doHandle();
    }

    private function doHandle()
    {
        $fencer = Fencer::find($this->fencerId);
        $event = Event::find($this->eventId);

        if (!empty($fencer) && !empty($event)) {
            $this->matchExistingAccreditations($fencer, $event);
        }
        else {
            // Else silently fail.
            // But always clear out any existing accreditations which may have caused this job
            Accreditation::where('fencer_id', $this->fencer_id)->where('event_id', $this->event_id)->delete();
            // This fails to delete the file accompanying the accreditations, but they can be removed
            // after the event by removing the entire event directory
        }
    }

    private function matchExistingAccreditations(Fencer $fencer, Event $event)
    {
        $newTemplates = (new AccreditationCreateService($fencer, $event))->handle();
        $matchService = new AccreditationMatchService($fencer, $event);
        $matchService->handle($newTemplates);
        $matchService->actualise();

        foreach ($matchService->foundAccreditations as $a) {
            if (!empty($a->is_dirty)) {
                $a->is_dirty = null; // make it clean to avoid additional jobs for this accreditation
                $a->save();
                $job = new CreateBadge($a);
                if ($this->executeSynchronously) {
                    try {
                        $job->handle();
                    }
                    catch(\Exception $e) {
                        \Log::error("caught exception trying to create a badge " . $e->getMessage());
                    }
                }
                else {
                    dispatch($job);
                }
            }
        }
    }
}
