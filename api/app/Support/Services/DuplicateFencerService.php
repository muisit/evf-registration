<?php

namespace App\Support\Services;

use App\Models\Fencer;
use Carbon\Carbon;

class DuplicateFencerService
{
    public function check($fencer): ?Fencer
    {
        $fencer = (array) $fencer;
        $date = (new Carbon($fencer["dateOfBirth"]))->toDateString();
        if (empty($fencer["dateOfBirth"])) {
            $date = null;
        }

        return Fencer::where('fencer_surname', $fencer["lastName"])
            ->where('fencer_firstname', $fencer["firstName"])
            ->where('fencer_dob', $date)
            ->where('fencer_id', '<>', $fencer["id"])
            ->first();
    }
}
