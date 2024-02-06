<?php

namespace App\Support\Services\Codes;

use App\Models\Accreditation;
use App\Models\AccreditationUser;
use App\Models\Schemas\Code;
use Illuminate\Support\Facades\Auth;

class LoginService
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
            // login by scanning an accreditation badge
            return $this->loginByBadge($code);
        }
        else if ($code->baseFunction == 9) {
            // login by scanning an admin code
            return $this->loginByCode($code);
        }
        return $this->manager->addError("Access denied");
    }

    private function loginByBadge(Code $code)
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

        // scanned code refers to an existing accreditation badge. See if this badge has an associated AccreditationUser
        $user = AccreditationUser::where('accreditation_id', $accreditations[0]->getKey())
            ->where('event_id', $this->manager->event->getKey())
            ->first();
        if (empty($user)) {
            return $this->manager->addError("Access denied");
        }
        request()->session()->flush();
        Auth::login($user); // this sets the correct session cookie to remember the AccreditationUser
        $this->manager->result->status = 'ok';

        // no need to check view-fencer authorization, this is the own user
        if (!empty($user->fencer)) $this->manager->result->setFencer($user->fencer);
        return $this->manager->result;
    }

    private function loginByCode(Code $code)
    {
        // scanning the admin code may imply switching the event
        $user = AccreditationUser::where('code', $code->original)
            ->where('event_id', intval($code->payload))
            ->where('accreditation_id', null)
            ->get();
        if (count($user) > 1) {
            return $this->manager->addError("Invalid code");
        }
        if (empty($user)) {
            return $this->manager->addError("Access denied");
        }
        $this->manager->setEvent($user[0]->event);
        $this->manager->action = "login";
        
        $user = $user[0];
        request()->session()->flush();
        Auth::login($user);
        $this->manager->result->status = 'ok';
        return $this->manager->result;
    }
}
