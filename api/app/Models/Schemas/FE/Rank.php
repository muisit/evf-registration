<?php

namespace App\Models\Schemas\FE;

use App\Models\RankingPosition;

class Rank
{
    public $id;
    public $name;
    public $firstname;
    public $country;
    public $points;
    public $pos;
    public $dob;

    public function __construct(RankingPosition $pos, $doExtended = false)
    {
        $this->id = $pos->fencer->uuid;
        $this->name = $pos->fencer->fencer_surname;
        $this->firstname = $pos->fencer->fencer_firstname;
        $this->country = $pos->fencer->country->country_abbr;
        $this->points = $pos->points;
        $this->pos = intval($pos->position);

        if ($doExtended) {
            $this->dob = $pos->fencer->fencer_birthdate;
        }
    }
}
