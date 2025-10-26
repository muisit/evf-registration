<?php

namespace App\Models\Schemas\FE;

use App\Models\Result as Model;

class Result
{
    public $id;
    public $competition_id;
    public $fencer_id;
    public $place;
    public $points;
    public $entry;
    public $de_points;
    public $podium_points;
    public $total_points;
    public $ranked;
    public $fencer_firstname;
    public $fencer_surname;
    public $fencer_dob;
    public $country_abr;
    public $country_id;
    public $event_name;
    public $event_country;
    public $event_date;
    public $category_name;
    public $category_value;
    public $weapon_abbr;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->competition_id = $data->result_competition;
        $this->fencer_id = $data->result_fencer;
        $this->place = $data->result_place;
        $this->points = $data->result_points;
        $this->entry = $data->result_entry;
        $this->de_points = $data->result_de_points;
        $this->podium_points = $data->result_podium_points;
        $this->total_points = $data->result_total_points;
        $this->ranked = $data->result_in_ranking;

        $this->fencer_firstname = $data->fencer->fencer_firstname;
        $this->fencer_surname = $data->fencer->fencer_surname;
        $this->fencer_dob = $data->fencer->fencer_dob;
        $this->country_abbr = $data->fencer->country->country_abbr;
        $this->country_id = $data->fencer->fencer_country;
        $this->event_name = $data->competition->event?->event_name ?? '';
        $this->event_country = $data->competition->event?->event_country ?? '';
        $this->event_date = $data->competition->event?->event_open ?? '';
        $this->category_name = $data->competition->category?->category_name ?? '';
        $this->category_value = $data->competition->category?->category_value ?? '';
        $this->weapon_abbr = $data->competition->weapon?->weapon_abbr ?? '';
    }
}
