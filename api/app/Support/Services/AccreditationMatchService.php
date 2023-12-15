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
        \Log::debug("found " . count($existingAccreditations) . " existing accreditations");
        $this->foundAccreditations = [];
        $foundIds = [];
        $newAccreditations = [];
        $this->missingAccreditations = [];
        foreach ($newData as $a1) {
            $foundThis = false;
            foreach ($existingAccreditations as $a2) {
                if ($a1['hash'] == $a2->hash) {
                    \Log::debug("hash matches, found this exact accreditation");
                    $foundIds[] = $a2->getKey();
                    $foundThis = true;
                    break; // only match the first accreditation if we happen to have duplicates
                }
            }
            if (!$foundThis) {
                \Log::debug("no match, new accreditation");
                $newAccreditations[] = $a1;
            }
        }

        foreach ($existingAccreditations as $a1) {
            if (!in_array($a1->getKey(), $foundIds)) {
                \Log::debug("existing accreditation not matched " . $a1->getKey());
                $this->missingAccreditations[] = $a1;
            }
            else {
                $this->foundAccreditations[] = $a1;
            }
        }

        // the found accreditations need to update their dirty value
        $this->foundAccreditations = collect($this->foundAccreditations)->map(function (Accreditation $a) {
            // check that the file actually exists and that we have an accreditation ID set
            $path = $a->path();
            if (!file_exists($path) || empty($a->fe_id)) {
                \Log::debug("creating new front end id");
                $a->createId();
            }
            else {
                // accreditation was found, the data still matches, so we do not need to regenerate
                \Log::debug("resetting is dirty value for existing accreditation " . $a->getKey());
                $a->is_dirty = null;
            }
            return $a;
        });

        // all new data needs to be converted to new accreditations
        foreach ($newAccreditations as $data) {
            \Log::debug("creating a new accreditation");
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