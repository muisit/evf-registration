<?php

namespace App\Support\Services;

use App\Models\Workflow;

class WorkflowService
{
    private Workflow $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function handle()
    {
        switch ($this->workflow->sandbox['name'] ?? 'generic') {
            case 'uploadXML':
                $this->handleUploadXML();
                break;
        }
    }

    private function handleUploadXML()
    {
        if (!isset($this->workflow->sandbox['step'])) {
            // initial step
            $sb = $this->workflow->sandbox;
            $sb['step'] = 'init';
            $this->workflow->sandbox = $sb;
            $this->workflow->save();
            return;
        }

        switch ($this->workflow->sandbox['step']) {
            case 'init':
                break;
        }
    }
}
