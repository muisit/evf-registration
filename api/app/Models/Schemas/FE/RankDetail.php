<?php

namespace App\Models\Schemas\FE;

use App\Models\Category;
use App\Models\Result;
use App\Models\Weapon;
use Carbon\Carbon;

class RankDetail
{
    public $id;
    public $surname;
    public $firstname;
    public $abbr;
    public $event;
    public $date;
    public $year;
    public $location;
    public $country;
    public $category;
    public $weapon;
    public $entry;
    public $place;
    public $points;
    public $de;
    public $podium;
    public $total;
    public $factor;
    public $included;

    public function __construct(Result $result, Category $cat = null, Weapon $wpn = null)
    {
        $this->id = $result->fencer->uuid;
        $this->surname = $result->fencer->fencer_surname;
        $this->firstname = $result->fencer->fencer_firstname;
        $this->abbr = $result->fencer->country->country_abbr;
        $this->event = $result->competition->event->event_name;
        $this->date = $result->competition->event->event_open;
        $this->year = (new Carbon($result->competition->event->event_open))->format('Y');
        $this->location = $result->competition->event->event_location;
        $this->country = $result->competition->event->country->country_name;
        $this->category = empty($cat) ? $result->competition->category->category_name : $cat->category_name;
        $this->weapon = empty($wpn) ? $result->competition->weapon->weapon_name : $wpn->weapon_name;
        $this->entry = intval($result->result_entry);
        $this->place = $result->result_place;
        $this->points = $result->result_points;
        $this->de = $result->result_de_points;
        $this->podium = $result->result_podium_points;
        $this->total = $result->result_total_points;
        $this->factor = $result->competition->event->event_factor ?? 1.0;
        $this->included = $result->result_in_ranking;
    }
}
