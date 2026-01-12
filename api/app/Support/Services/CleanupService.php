<?php

namespace App\Support\Services;

use App\Models\Fencer;
use App\Models\Workflow;
use Illuminate\Support\Str;

class CleanupService
{
    public function handle()
    {
        $flows = Workflow::all();
        foreach ($flows as $flow) {
            // delete the flow and any associated files
            // We do not retain flows of yesterday, assuming any workflow should
            // be finished before this task runs
            $flow->delete();
        }

        // it may so happen that a fencer is created without a uuid. This causes issues
        // in the front-end applications, so we make sure all fencers have a proper uuid
        $fencers = Fencer::where('uuid', null)->get();
        foreach ($fencers as $fencer) {
            $fencer->uuid = Str::uuid()->toString();
            $fencer->save();
        }

    }
}
