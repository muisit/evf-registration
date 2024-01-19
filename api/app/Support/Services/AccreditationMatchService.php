<?php

namespace App\Support\Services;

use App\Models\Accreditation;
use App\Models\Fencer;
use App\Models\Event;

class AccreditationMatchService
{
    public $missingAccreditations = [];
    public $foundAccreditations = [];

    public function __construct(Fencer $fencer, Event $event)
    {
        $this->fencer = $fencer;
        $this->event = $event;
    }

    // match the new accreditation templates with the data stored in existing accreditations
    public function handle(array $newData)
    {
        $newData = collect($newData)->map(function ($data) {
            $data['hash'] = $this->makeHash($data['content']);
            return $data;
        })->toArray();

        $existingAccreditations = Accreditation::where('fencer_id', $this->fencer->getKey())->where('event_id', $this->event->getKey())->get();
        $this->foundAccreditations = [];
        $foundIds = [];
        $newAccreditations = [];
        $this->missingAccreditations = [];
        foreach ($newData as $a1) {
            $foundThis = false;
            foreach ($existingAccreditations as $a2) {
                if ($a2->template_id == $a1['template']->getKey()) {
                    if ($a1['hash'] !== $a2->hash) {
                        $a2->data = json_encode($a1['content']);
                        $a2->hash = $a1['hash'];
                        $a2->is_dirty = date('Y-m-d H:i:s');
                    }
                    else {
                        $a2->is_dirty = null;
                    }
                    $foundIds[] = $a2->getKey();
                    $foundThis = true;
                    break; // only match the first accreditation if we happen to have duplicates
                }
            }

            if (!$foundThis) {
                $newAccreditations[] = $a1;
            }
        }

        foreach ($existingAccreditations as $a1) {
            if (!in_array($a1->getKey(), $foundIds)) {
                $this->missingAccreditations[] = $a1;
            }
            else {
                $this->foundAccreditations[] = $a1;
            }
        }

        // the found accreditations need to update their dirty value
        $this->foundAccreditations = collect($this->foundAccreditations);

        // all new data needs to be converted to new accreditations
        foreach ($newAccreditations as $data) {
            $accreditation = new Accreditation();
            $accreditation->event_id = $this->event->getKey();
            $accreditation->fencer_id = $this->fencer->getKey();
            $accreditation->template_id = $data["template"]->getKey();
            $accreditation->data = json_encode($data['content']);
            $accreditation->hash = $data['hash'];
            $accreditation->is_dirty = date('Y-m-d H:i:s'); // make dirty, so we regenerate
            $this->foundAccreditations[] = $accreditation;
        }
    }

    public function actualise()
    {
        foreach ($this->missingAccreditations as $a) {
            $a->delete();
        }
        foreach ($this->foundAccreditations as $a) {
            $a->save();
        }
    }

    private function makeHash($template)
    {
        $val = $this->dataToString($template);
        return hash('sha256', $val, false);
    }

    private function dataToString($template)
    {
        // sort all array keys to make sure they are in the same order.
        // then concatenate all the values to the keys.
        // Any change in case or value will be detected, but changes in
        // variable order within arrays are corrected
        if (is_array($template)) {
            $keys = array_keys($template);
            sort($keys);
            $retval = "";
            foreach ($keys as $k) {
                $retval .= $k . ":" . $this->dataToString($template[$k]);
            }
            return $retval;
        }
        else {
            return strval($template);
        }
    }
}