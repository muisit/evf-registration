<?php

namespace App\Models\Schemas\FE;

use App\Models\Category;
use App\Models\Event;
use App\Models\Weapon;
use App\Models\Fencer as Model;
use App\Models\Ranking;
use App\Models\Registration;
use DateTimeImmutable;

class Fencer
{
    public $id;
    public $firstname;
    public $name;
    public $country;
    public $country_name;
    public $birthday;
    public $gender;
    public $picture;
    public $basic = null;

    public function __construct(Model $data, $extensive = false)
    {
        $this->id = $data->getKey();
        $this->firstname = $data->fencer_firstname;
        $this->name = $data->fencer_surname;
        $this->country = $data->fencer_country;
        $this->country_name = $data->country_name ?? $data->country->country_name;
        $this->birthday = $data->fencer_dob;
        $this->gender = $data->fencer_gender;
        $this->picture = $data->fencer_picture;

        if ($extensive) {
            $this->basic = [];
            $this->basic["rankings"] = $this->getRankings($data);
            $this->basic["registrations"] = $this->getRegistrations($data);
        }
    }

    private function getRankings(Model $data)
    {
        $category = $this->getCurrentCategory($data);
        if (empty($category)) {
            return null;
        }

        $retval = array();
        foreach (array("E","F","S") as $wpn) {
            $weapon = $data->gender == 'F' ? 'W' . $wpn : 'M' . $wpn;
            $ranking = $this->getRankingForWeapon($data, $category, $weapon);
            if (!empty($ranking)) {
                $retval[$weapon] = $ranking;
            }
        }
        return $retval;
    }

    private function getRegistrations(Model $data)
    {
        $regs = Registration::where('registration_fencer', $data->getKey())
            ->join(Event::tableName() . ' AS ev', 'ev.event_id', '=', Registration::tableName() . '.registration_mainevent')
            ->where("ev.event_open", ">", date('Y-m-d'))
            ->get();

        $retval = array();
        if (!empty($regs)) {
            foreach ($regs as $reg) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $reg->event_open);
                $retval[$reg->registration_mainevent] = array(
                    $reg->event_name,
                    $date->format('Y')
                );
            }
        }
        return $retval;
    }

    private function getRankingForWeapon(Model $data, $category, $weapon)
    {
        $weapon = Weapon::where('weapon_abbr', $weapon)->first();
        if (empty($weapon)) {
            return null;
        }

        $ranking = Ranking::where('category_id', $category->getKey())->where('weapon_id', $weapon->getKey())->orderBy('updated_at', 'desc')->first();
        if (empty($ranking)) {
            return null;
        }

        $pos = $ranking->positions()->where('fencer_id', $data->getKey())->first();
        if (empty($pos)) {
            return null;
        }
        return [
            "pos" => $pos->position,
            "points" => $pos->points
        ];
    }

    private function getCurrentCategory(Model $data)
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $data->fencer_dob);
        if ($date === false) {
            return null;
        }
        $catnum = Category::categoryFromYear($date->format('Y'), date('Y-m-d'));
        if ($catnum < 1) {
            return null;
        }
        return Category::where('category_value', $catnum)->first();
    }
}
