<?php

namespace App\Support\Services;

use App\Models\Category;
use App\Models\Country;
use App\Models\Weapon;
use Carbon\Carbon;

class FIEXMLService
{
    private string $path;
    private $document;
    private $encoding = 'utf-8';
    private $countries;

    public $fencers = [];
    public $competition = [];

    public function __construct($path)
    {
        $this->path = $path;
        $this->countries = Country::all()->mapWithKeys(function ($item, $key) {
            return [$item->country_abbr => $item];
        });
    }

    public function handle()
    {
        $content = file_get_contents($this->path);
        $this->document = new \DOMDocument("1.0");
        $this->document->loadXML($content);
        //\Log::debug("loaded XML: ". $this->document->encoding . '/' . $this->document->xmlEncoding);
        //\Log::debug("children " . count($this->document->childNodes));
        $this->encoding = str_replace('-', '', strtolower($this->document->encoding));

        // CompetitionIndividuelle for results of individual competitions
        // CompetitionParEquipes for results of team competitions
        foreach ($this->document->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                //\Log::debug("testing  " . $node->nodeName . '/ ' . $node->nodeType);
                switch ($node->nodeName) {
                    case 'CompetitionIndividuelle':
                        //\Log::debug("Found the competition");
                        $this->parseCompetition($node);
                        break;
                }
            }
        }

        //$output = [
        //        'weapon' => $this->competition['weapon']?->weapon_name ?? null,
        //        'category' => $this->competition['category']?->category_name ?? null,
        //        'gender' => $this->competition['gender'] ?? null,
        //        'date' => $this->competition['date']?->format('Y-m-d') ?? null,
        //        'location' => $this->competition['location'] ?? null,
        //        'name' => $this->competition['name'] ?? null                
        //];
        //\Log::debug('Competition : ' . json_encode($output));
    }

    private function parseCompetition($parent)
    {
        foreach ($parent->attributes as $name => $attr) {
            $value = $attr->value;
            switch (strtolower($name)) {
                case 'arme':
                    $this->competition['weapon_base'] = $value;
                    break;
                case 'sexe':
                    $this->competition['gender'] = $this->convertGender($value);
                    break;
                case 'categorie':
                    $this->competition['category'] = $this->convertCategory($value);
                    break;
                case 'date':
                    $this->competition['date'] = $this->convertDate($value);
                    break;
                case 'lieu':
                    $this->competition['location'] = $this->convertString($value);
                    break;
                case 'titrelongtournoi':
                    $this->competition['name'] = $this->convertString($value);
                    break;
                case 'federation':
                    $this->competition['country'] = $this->convertCountry($value);
                    break;
            }
        }
        $this->competition['weapon'] = $this->convertWeapon();

        foreach ($parent->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                //\Log::debug("competition node " . $node->nodeName);

                switch (strtolower($node->nodeName)) {
                    case 'tireurs':
                        //\Log::debug("parsing list of fencers");
                        $this->parseFencers($node);
                        break;
                    case 'arbitres':
                        // no need for referees at this stage
                        break;
                    case 'phases':
                        // no need to replay the tournament
                        break;
                }
            }
        }
        //\Log::debug("fencers " . count($this->fencers));
        //\Log::debug(json_encode(collect($this->fencers)->map(function ($item) {
        //    return [
        //        'id' => $item['id'] ?? -1,
        //        'ophardt' => $item['ophardt'] ?? null,
        //        'firstname' => $item['firstname'],
        //        'lastname' => $item['lastname'],
        //        'gender' => $item['gender'] ?? 'M',
        //        'dob' => isset($item['dob']) ? $item['dob']->format('Y-m-d') : null,
        //        'country' => isset($item['country']) ? $item['country']->country_abbr : null,
        //        'result' => $item['result'] ?? 'dnf'
        //    ];
        //}), JSON_PRETTY_PRINT, 2));
    }

    private function parseFencers($parent)
    {
        foreach ($parent->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                //\Log::debug("fencer list " . $node->nodeName);
                if (strtolower($node->nodeName) == 'tireur') {
                    $this->parseFencer($node);
                }
            }
        }
    }

    private function parseFencer($node)
    {
        //\Log::debug("parsing fencer node");
        $fencer = [];
        foreach ($node->attributes as $name => $attr) {
            $value = $attr->value;
            //\Log::debug("fencer node attribute " . $name . ' = ' . $value);
            switch (strtolower($name)) {
                case 'id':
                    $fencer['id'] = $value;
                    break;
                case 'ophardt_id':
                    $fencer['ophardt'] = $value;
                    break;
                case 'nom':
                    $fencer['lastname'] = $this->convertString($value);
                    break;
                case 'prenom':
                    $fencer['firstname'] = $this->convertString($value);
                    break;
                case 'datenaissance':
                    $fencer['dob'] = $this->convertDate($value);
                    break;
                case 'sexe':
                    $fencer['gender'] = $this->convertGender($value);
                    break;
                case 'nation':
                    $fencer['country'] = $this->convertCountry($value);
                    break;
                case 'classement':
                    $fencer['result'] = intval($value);
                    break;
            }
        }
        // it seems Ophardt keeps scratched fencers in the entry list
        // or they may be drop-outs from a combined earlier round, but we 
        // do not have the results of the combined round in this simple XML probably
        if (!empty($fencer['result']) && intval($fencer['result']) > 0) {
            //\Log::debug("adding fencer to list of fencers");
            $this->fencers[] = $fencer;
        }
    }

    private function convertCountry($cnt)
    {
        if (isset($this->countries[$cnt])) {
            return $this->countries[$cnt];
        }
        return $this->countries['OTH']; // Other
    }

    private function convertGender($val)
    {
        return $val === 'F' ? 'F' : 'M';
    }

    private function convertDate($val)
    {
        $els = explode('.', $val);
        $date = Carbon::createFromDate($els[2], intval($els[1]), intval($els[0]));
        return $date;
    }

    private function convertString($txt)
    {
        if ($this->encoding == 'iso88591') {
            return mb_convert_encoding($txt, 'utf-8', 'iso-8859-1');
        }
        return $txt;
    }

    private function convertWeapon()
    {
        $base = $this->competition['weapon_base'] ?? null;
        $gender = $this->competition['gender'] ?? null;
        if (!empty($base) && !empty($gender)) {
            $gender = $gender == 'F' ? 'W' : 'M';
            $abbr = $gender . $base;
            return Weapon::where('weapon_abbr', $abbr)->first();
        }
        return null;
    }

    private function convertCategory($value)
    {
        //\Log::debug("converting category $value");
        switch (strtolower($value)) {
            case 'o40':
                return Category::find(Category::CAT1);
            case 'o50':
                return Category::find(Category::CAT2);
            case 'o60':
                return Category::find(Category::CAT3);
            case 'o70':
                return Category::find(Category::CAT4);
        }
        return null;
    }
}

