<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// This job checks to see if all available accreditations for a fencer are still
// matching as far as data is concerned. It will remove accreditations that are
// no longer required, add new accreditations if required and create appropiate
// CreateBadge jobs for accreditations that have changed or have been added.
//
// When accreditations are removed, the accompanying files are removed as well
//
class CheckSummaries extends Job implements ShouldBeUniqueUntilProcessing
{
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
        $documents = Document::where('id', '>', 0)->get();
        foreach ($documents as $document) {
            // check on empty events to clean out old documents
            if (empty($document->event) || !$document->event->exists || !$document->validate()) {
                $document->deleteSiblings();
            }
        }
    }
}
