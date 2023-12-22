<?php

namespace App\Jobs;

use App\Models\Country;
use App\Models\Role;
use App\Models\AccreditationTemplate;
use App\Models\SideEvent;
use App\Models\Event;
use App\Support\Services\PDFService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// This job sets up the various CreateSummary jobs based on the collection
// of accreditations for this Summary type.
class SetupSummary extends Job implements ShouldBeUniqueUntilProcessing
{
    public Event $event;
    public string $type;
    public int $typeId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Event $event, string $type, int $typeId)
    {
        $this->event = $event->withoutRelations();
        $this->type = $type;
        $this->typeId = $typeId;
    }

    public function uniqueId(): string
    {
        return $this->summaryName();
    }

    private function summaryName(): string
    {
        return $this->event->getKey() . '_' . $this->type . "_" . $this->typeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!in_array($this->type, ["Country", "Role", "Template", "Event"])) {
            $this->fail("Invalid summary type set: $this->type");
        }

        $model = PDFService::modelFactory($this->type, $this->typeId);
        if (!$model->exists && ($this->type != 'Role' || $this->typeId != 0)) {
            $this->fail("Invalid type model, cannot create PDF summary for $this->type/$this->typeid");
        }

        $documents = PDFService::split($this->event, $this->type, $model);
        foreach ($documents as $doc) {
            dispatch(CreateSummary($doc));
        }
    }

}
