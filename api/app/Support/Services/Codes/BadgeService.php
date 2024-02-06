<?php

namespace App\Support\Services\Codes;

use App\Models\Accreditation;
use App\Models\AccreditationUser;
use App\Models\Schemas\Code;
use Illuminate\Support\Facades\Auth;

class BadgeService
{
    public function __construct(CodeService $manager)
    {
        $this->manager = $manager;
    }

    public function handle(array $codes)
    {
        if (count($codes) != 1) {
            return $this->manager->addError("Invalid parameters");
        }
        $code = $codes[0];

        if ($code->baseFunction == 1) {
            // return fencer information based on badge
            return $this->findFencer($code);
        }
        return $this->manager->addError("Access denied");
    }

    private function findFencer(Code $code)
    {
        $fe_id = sprintf("%d%3d%3d", $code->addFunction, $code->id1, $code->id2);
        $accreditations = Accreditation::where('fe_id', $fe_id)->get();
        
        if (count($accreditations) != 1) {
            return $this->manager->addError("Invalid code");
        }

        if ($this->manager->event->exists && $accreditations[0]->event_id != $this->manager->event->getKey()) {
            // scanning a badge from a different event, that happens to have rights still
            return $this->manager->addError("Access denied");
        }
        // override the event so it is set properly in the result status as well
        $this->manager->setEvent($accreditations[0]->event);

        if (empty($accreditations[0]->fencer)) {
            return $this->manager->addError("Access denied");
        }
        $fencer = $accreditations[0]->fencer;

        if (Auth::user()->can('view', $fencer)) {
            $this->manager->result->setFencer($fencer);
        }
        $this->manager->result->status = 'ok';
        return $this->manager->result;
    }
}
