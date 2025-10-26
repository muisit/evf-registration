<?php

namespace App\Support\Services;

use App\Models\Fencer;
use App\Models\FencerLabel;

class FencerLabelService
{
    public function extendFencer(Fencer $fencer, $newfirstname, $newlastname)
    {
        $existing = $fencer->labels()->where('type', 'first')->where('label', $newfirstname)->first();
        if (!$existing) {
            $label = new FencerLabel();
            $label->fencer_id = $fencer->getKey();
            $label->type = 'first';
            $label->label = $newfirstname;
            $label->save();
        }

        $existing = $fencer->labels()->where('type', 'last')->where('label', $newlastname)->first();
        if (!$existing) {
            $label = new FencerLabel();
            $label->fencer_id = $fencer->getKey();
            $label->type = 'last';
            $label->label = $newlastname;
            $label->save();
        }
    }

    public function updateFencer(Fencer $fencer, $newfirstname, $newlastname)
    {
        if (strcmp($fencer->fencer_firstname, $newfirstname)) {
            $this->updateLabel($fencer, $fencer->fencer_firstname, $newfirstname, 'first');
        }
        if (strcmp($fencer->fencer_firstname, $newfirstname)) {
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
