<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Role;
use App\Models\AccreditationTemplate;
use App\Models\SideEvent;
use App\Models\Event;
use App\Support\Services\PDFService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// This job sets up the various CreateSummary jobs based on the collection
// of accreditations for this Summary type.
class CreateSummary extends SetupSummary
{
    public Document $document;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Document $document)
    {
        $this->document = $document->withoutRelations();
    }

    public function uniqueId(): string
    {
        return $this->document->getKey();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (file_exists($this->document->getPath())) {
            @unlink($this->document->getPath());
        }
        if (file_exists($this->document->getPath())) {
            $this->fail("Could not remove old summary document from " . $this->document->getPath());
        }
        else {
            PDFService::createSummary($this->document);
            if (!file_exists($this->document->getPath())) {
                $this->fail("Could not create PDF at " . $document->getPath());
            }
        }
    }
}
