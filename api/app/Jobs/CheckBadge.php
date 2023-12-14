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
    public function handle()
    {
    }
}
