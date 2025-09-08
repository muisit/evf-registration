<?php

namespace App\Support\Services;

use App\Models\Competition;
use App\Models\Country;
use App\Models\Fencer;
use App\Models\FencerLabel;
use Carbon\Carbon;
use DB;

class ImportCheckService
{
    // of course rather ugly, but we make everything public to make it unit testable
    public $weapon;
    public $gender;
    public $category;
    public $minDate;
    public $maxDate;

    public $countryCache = [];

    public function handle(Competition $competition, $ranking)
    {
        // ranking consists of a list of pos,lastname,firstname,country values
        // Check for each entry that the combination of lastname, firstname, country exists
        $retval = array("ranking" => array());

        $this->weapon = $competition->weapon;
        $this->gender = $weapon->weapon_gender;
        $this->category = $competition->category;
        $this->minDate = $category->getMinimalDate($competition->event->event_opens);
        $this->maxDate = $category->getMaximalDate($competition->event->event_opens);
        $this->createCountryCache();

        $retval = [];
        foreach ($ranking as $entry) {
            $retval[] = $this->handleEntry($entry);
        }
        return $retval;
    }

    public function createCountryCache()
    {
        $countries = Country::where('country_id', '>', 0)->get();
        foreach ($countries as $c) {
            $this->countryCache[$c->country_name] = $c;
            $this->countryCache[$c->country_abbr] = $c;
            $this->countryCache['c' . $c->getKey()] = $c;
        }
    }

    private function sanitizeName($value)
    {
        // trim whitespace in front and after
        $value = preg_replace("/(^\s+)|(\s+$)/u", "", $value);
        // replace any numeric, non-lexical, non-space characters
        // keep dashes, points, acolades
        $value = preg_replace("/[^-. '\p{L}]/u", " ", $value);
        return $value;
    }

    private function findCountry($field)
    {
        if (isset($this->countryCache[$field])) {
            return $this->countryCache[$field];
        }
        return $this->countryCache['OTH']; // Others
    }

    public function findFencerByNameAndGender($f, $l)
    {
        $fencers = Fencer::where('fencer_gender', $this->gender)
            ->whereExists(function ($query) use ($f) {
                $query->select(DB::Raw('*'))
                    ->from(FencerLabel::tableName())
                    ->whereColumn(Fencer::tableName() . '.fencer_id', FencerLabel::tableName() . '.fencer_id')
                    ->where('type', 'first')
                    ->where('label', $f);
            })
            ->whereExists(function ($query) use ($l) {
                $query->select(DB::Raw('*'))
                    ->from(FencerLabel::tableName())
                    ->whereColumn(Fencer::tableName() . '.fencer_id', FencerLabel::tableName() . '.fencer_id')
                    ->where('type', 'last')
                    ->where('label', $l);
            })
            ->get();
        return $fencers;
    }

    public function findEntryForName($first, $last, $country)
    {
        $results = [];
        $fencers = $this->findFencerByNameAndGender($first, $last);
        // see if any of these fencers is from the indicated country
        foreach ($fencers as $f) {
            $fc = $this->findCountry('c' . $f->fencer_country);
            if ($fc->getKey() == $country->getKey()) {
                if (!$this->matchDates($f->fencer_dob, $this->minDate, $this->maxDate)) {
                    \Log::debug("correct match, but the date is off. See if the fencer is too old perhaps");
                    if ($this->matchDates($f->fencer_dob, null, $this->minDate)) {
                        \Log::debug("fencer is too old for this category, but that is okay");
                        $results[] = ["fencer" => $f, "checks" => [["type" => "age", "message" => "Person is too old for this category"]]];
                    }
                    else {
                        \Log::debug("fencer is too young, skipping this entry");
                    }
                }
                else {
                    \Log::debug("found a name, country, age, gender match");
                    $results = [["fencer" => $f]];
                    break;
                }
            }
        }

        // if we found 0, search on. If we found > 1, Neo is very likely one of them, so we don't need to look
        // in other countries
        if (count($results) < 1) {
            // no match, see if Neo is from a different country. There can be more Neo's, of course
            foreach ($fencers as $f) {
                $fc = $this->findCountry('c' . $f->fencer_country);
                if ($fc->getKey() !== $country->getKey()) {
                    \Log::debug("checking to see if Neo is from a different country");
                    if ($this->matchDates($f->fencer_dob, $this->minDate, $this->maxDate)) {
                        $results[] = ["fencer" => $f, "checks" => [["type" => "country", "message" => "Incorrect country"]]];
                    }
                    else if ($this->matchDates($f->fencer_dob, null, $this->minDate)) {
                        $results[] = ["fencer" => $f, "checks" => [
                            ["type" => "country", "message" => "Incorrect country"],
                            ["type" => "age", "message" => "Person is too old for this category"]
                        ]];
                    };
                }
            }
        }
        return $results;
    }

    public function findAllByLabelSound($type, $label)
    {
        return Fencer::whereExists(function ($query) use ($type, $label) {
            return $query->select(DB::Raw('*'))->from(FencerLabel::tableName())
                ->where("type", $type)
                ->whereColumn(FencerLabel::tableName() . '.fencer_id', Fencer::tableName() . '.fencer_id')
                ->where(DB::Raw("SOUNDEX(label)"), '=', DB::Raw("SOUNDEX('$label')"));
        })->where('fencer_gender', $this->gender)->get();
    }

    public function findSuggestions($firstname, $lastname, $country)
    {
        $matchLastName = $this->findAllByLabelSound('last', $lastname);
        $matchLastNameAge = $matchLastName->filter(fn ($item) => $this->matchDates($item->fencer_dob, null, $this->maxDate));
        $matchFirstName = $this->findAllByLabelSound('first', $firstname);
        $matchFirstNameAge = $matchFirstName->filter(fn ($item) => $this->matchDates($item->fencer_dob, null, $this->maxDate));
        $matchCountry = Fencer::where('fencer_country', $country->getKey())->get();
        $matchCountryAge = $matchCountry->filter(fn ($item) => $this->matchDates($item->fencer_dob, null, $this->maxDate));

        $lnIds = $matchLastNameAge->pluck('fencer_id')->toArray();
        $fnIds = $matchFirstNameAge->pluck('fencer_id')->toArray();
        $cnIds = $matchCountryAge->pluck('fencer_id')->toArray();
        $lnIdsAll = $matchLastName->pluck('fencer_id')->toArray();
        $fnIdsAll = $matchFirstName->pluck('fencer_id')->toArray();
        $cnIdsAll = $matchCountry->pluck('fencer_id')->toArray();

        $m = array_intersect($lnIds, $fnIds, $cnIds); // should be 0
        $m1 = array_intersect($lnIds, $fnIds);
        $m2 = array_intersect($lnIds, $cnIds);
        $m3 = array_intersect($fnIds, $cnIds);

        // these are all the 'all' fencers that are not in the regular selections (soo the 'too young' fencers)
        $mb = array_diff($m, array_intersect($lnIdsAll, $fnIdsAll, $cnIdsAll));
        $m1b = array_diff($m1, array_intersect($lnIdsAll, $fnIdsAll));
        $m2b = array_diff($m2, array_intersect($lnIdsAll, $cnIdsAll));
        $m3b = array_diff($m3, array_intersect($fnIdsAll, $cnIdsAll));

        // add to the keys until we have enough suggestions (at least 10)
        $keys = $m;
        if (count($keys) < 10) {
            // add all suggestions that match 2 out of 3 fields
            $keys = array_unique(array_merge($keys, $m1, $m2, $m3));
        }
        if (count($keys) < 10) {
            // add all lastname matches
            $keys = array_unique(array_merge($keys, $lnIds));
        }
        if (count($keys) < 10) {
            // add all firstname matches
            $keys = array_unique(array_merge($keys, $fnIds));
        }
        if (count($keys) < 10) {
            // add all suggestions that match all 3 fields, but are too young
            $keys = array_unique(array_merge($keys, $mb));
        }
        if (count($keys) < 10) {
            // add all suggestions that match 2 out of 3 fields, but are too yong
            $keys = array_unique(array_merge($keys, $m1b, $m2b, $m3b));
        }
        if (count($keys) < 10) {
            // add all lastname matches
            $keys = array_unique(array_merge($keys, $lnIdsAll));
        }
        if (count($keys) < 10) {
            // add all firstname matches
            $keys = array_unique(array_merge($keys, $fnIdsAll));
        }
        if (count($keys) < 10) {
            // add all people from the country that are old enough
            $keys = array_unique(array_merge($keys, $cnIds));
        }
        if (count($keys) < 10) {
            // add all other people from this country
            $keys = array_unique(array_merge($keys, $cnIdsAll));
        }
        // the next step would be to add all people... which is useless

        $retval = [];
        $a1 = $matchLastName->keyBy(fn ($item) => 'f' . $item->getKey());
        $a2 = $matchFirstName->keyBy(fn ($item) => 'f' . $item->getKey());
        $a3 = $matchCountry->keyBy(fn ($item) => 'f' . $item->getKey());
        $allFencers = collect()->union($a1)->union($a2)->union($a3);
        $allIdsOfAge = array_unique(array_merge($lnIds, $fnIds, $cnIds));
        foreach ($keys as $id) {
            $fencer = $allFencers['f' . $id];
            $value = ["fencer" => $fencer, 'checks' => []];
            if (!in_array($id, $lnIdsAll)) {
                $value['checks'][] = ["type" => "lastname", "message" => "Incorrect last name"];
            }
            if (!in_array($id, $fnIdsAll)) {
                $value['checks'][] = ["type" => "firstname", "message" => "Incorrect first name"];
            }
            if (!in_array($id, $cnIdsAll)) {
                $value['checks'][] = ["type" => "country", "message" => "Incorrect country"];
            }
            if (!in_array($id, $allIdsOfAge)) {
                $value['checks'][] = ["type" => "age", "message" => "Fencer is too young"];
            }
            $retval[] = $value;
        }
        return $retval;
    }

    public function handleEntry($entry)
    {
        // we leave 'position' as it is: an integer front-end check can be done there without problem
        $lastname = $this->sanitize($entry["lastname"]);
        $firstname = $this->sanitize($entry["firstname"]);
        $country = $this->findCountry($this->sanitize($entry["country"]));

        $results = $this->findEntryForName($firstname, $lastname, $country);
        if (count($results) == 0) {
            $results = $this->findSuggestions($firstname, $lastname, $country);
        }

        $values = array(
            "index" => $entry["index"],
            "fencer_id" => count($results) == 1 ? $results[0]['fencer']->getKey() : -1,
            "suggestions" => $results
        );
        return $values;
    }

    private function matchDates($dt, $min, $max)
    {
        \Log::debug("matching date $dt against " . $min?->format('Y-m-d') . ' and ' . $max?->format('Y-m-d'));
        $tm1 = Carbon::createFromFormat('Y-m-d', $dt);
        return ($min === null || $tm1 >= $min) && ($max === null || $tm1 < $max);
    }
}
