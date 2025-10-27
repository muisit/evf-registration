<?php

namespace App\Support\Services;

use App\Models\Category;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Ranking;
use App\Models\Result;
use App\Models\Weapon;
use Illuminate\Support\Facades\DB;

class RankingService
{
    private $category;
    private $weapon;

    public function __construct(Category $category, Weapon $weapon)
    {
        $this->category = $category;
        $this->weapon = $weapon;
    }

    public function generate()
    {
        // determine the minimal and maximal year-of-birth values for the indicated category
        $ages = $this->calculateCategoryAges();
        $rows = DB::table('VW_Ranking')
            ->select(["fencer_id", "fencer_surname", "fencer_firstname", "fencer_country_abbr", DB::Raw("sum(result_total_points) as total_points")])
            ->whereRaw("(year(fencer_dob) > '" . $ages[0] . "' and year(fencer_dob) <= '" . $ages[1] . "')")
            ->where("weapon_id", $this->weapon->getKey())
            ->where('result_in_ranking', 'Y')
            ->where('fencer_country_registered', 'Y')
            ->groupBy(["fencer_id", "fencer_surname", "fencer_firstname", "fencer_country_abbr"])
            ->orderBy("total_points", "desc")
            ->orderBy("fencer_surname")
            ->orderBy("fencer_firstname")
            ->orderBy("fencer_id")->get();

        $retval = [];
        $pos = 1;
        $effectivepos = 0;
        $lastpoints = -1.0;
        foreach ($rows as $r) {
            $points = sprintf("%.2f", floatval($r->total_points));
            $effectivepos += 1;
            // never true for the first entry
            if (floatval($points) < floatval($lastpoints)) {
                $pos = $effectivepos;
            }
            $lastpoints = $points;
            $entry = array(
                "id" => $r->fencer_id,
                "name" => $r->fencer_surname,
                "firstname" => $r->fencer_firstname,
                "country" => $r->fencer_country_abbr,
                "points" => $points,
                "pos" => $pos
            );
            $retval[] = $entry;
        }
        return $retval;
    }

    private function calculateCategoryAges()
    {
        $catval = intval($this->category->category_value);

        // determine the qualifying year
        $qualifying_year = intval(date('Y'));
        if (intval(date('m')) > 7) {
            $qualifying_year += 1;
        }

        $minyear = 0;
        $maxyear = $qualifying_year;

        switch ($catval) {
            default:
            case 1:
                $minyear = $qualifying_year - 50;
                $maxyear = $qualifying_year - 40;
                break;
            case 2:
                $minyear = $qualifying_year - 60;
                $maxyear = $qualifying_year - 50;
                break;
            case 3:
                $minyear = $qualifying_year - 70;
                $maxyear = $qualifying_year - 60;
                break;
            case 4:
                $minyear = 0;
                $maxyear = $qualifying_year - 70;
                break;
            // category not supported anymore...
            case 5:
                $minyear = 0;
                $maxyear = $qualifying_year - 80;
                break;
        }
        return [$minyear, $maxyear];
    }

    public static function latest(Category $cat, Weapon $wpn)
    {
        return Ranking::where('category_id', $cat->getKey())->where('weapon_id', $wpn->getKey())->orderBy('ranking_date', 'desc')->first();
    }

    public static function details(Fencer $fencer, Category $cat, Weapon $wpn)
    {
        $ranking = self::latest($cat, $wpn);
        if (empty($ranking)) {
            return null;
        }
        $position = $ranking->positions()->where('fencer_id', $fencer->getKey())->first();
        if (empty($position)) {
            return null;
        }
        $included = $position->settings['included'];
        $excluded = $position->settings['excluded'];
        $resultIds = array_merge($included, $excluded);
        $results = Result::whereIn('result_id', $resultIds)
            ->joinRelationship('competition.event')
            ->with('competition')
            ->with('competition.event')
            ->with('competition.event.country')
            ->with('fencer')
            ->with('fencer.country')
            ->orderBy('result_total_points', 'desc')
            ->orderBy(Event::tableName() . '.event_open', 'desc')
            ->orderBy(Event::tableName() . '.event_name', 'asc')
            ->get()
            ->map(function ($r) use ($included, $excluded) {
                if (!in_array($r->result_in_ranking, ['Y','N'])) {
                    // pass, keep excluded
                }
                else if (in_array($r->getKey(), $included)) {
                    $r->result_in_ranking = 'Y';
                }
                else //if (in_array($r->getKey(), $excluded))
                {
                    // by default, exclude
                    $r->result_in_ranking = 'N';
                }
                return $r;
            });

        return $results;
    }
}
