<?php

namespace App\Support\Services;

use App\Models\Fencer;
use App\Models\FencerLabel;

class FencerLabelService
{
    public function updateFencer(Fencer $fencer, $newfirstname, $newlastname)
    {
        if (!strcmp($fencer->fencer_firstname, $newfirstname)) {
            $this->updateLabel($fencer, $fencer->fencer_firstname, $newfirstname, 'first');
        }
        if (!strcmp($fencer->fencer_firstname, $newfirstname)) {
            $this->updateLabel($fencer, $fencer->fencer_surname, $newlastname, 'last');
        }
    }

    private function updateLabel(Fencer $fencer, $old, $new, $type)
    {
        $existing = $fencer->labels()->where('type', $type)->where('label', $old)->first();
        if (!empty($existing)) {
            $existing->label = $new;
            $existing->save();
        }
        else {
            // for new Fencer's, automatically create the name labels
            $label = new FencerLabel();
            $label->fencer_id = $fencer->getKey();
            $label->type = $type;
            $label->label = $new;
            $label->save();
        }
    }
}
