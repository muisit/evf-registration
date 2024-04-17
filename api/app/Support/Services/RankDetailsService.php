<?php

namespace App\Support\Services;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Weapon;
use App\Models\Ranking;
use App\Models\Result;
use App\Models\Schemas\RankDetails;
use App\Models\Schemas\Result as ResultSchema;

class RankDetailsService
{
    private $fencer;
    private $weapon;

    public function __construct(Fencer $fencer, Weapon $weapon)
    {
        $this->fencer = $fencer;
        $this->weapon = $weapon;
    }

    public function generate(): ?RankDetails
    {
        // Find the current category of this fencer
        // To prevent issues with people looking at the ranking on january 1st, after they have switched categories,
        // we do not calculate the category, but look at their latest position in any recent ranking for this weapon
        $pos = Ranking::select('rankings.*', 'ranking_positions.*')
            ->where('weapon_id', $this->weapon->getKey())
            ->joinRelationship('positions')
            ->where('ranking_positions.fencer_id', $this->fencer->getKey())
            ->first()
            ?->toArray();
        if (empty($pos)) {
            // no results found for this fencer
            return null;
        }

        $category = Category::find($pos['category_id']);
        
        $schema = new RankDetails();
        $schema->fencer = $this->fencer->uuid;
        $schema->weapon = $this->weapon->weapon_abbr;
        $schema->category = $category->category_abbr;
        $schema->date = $pos['ranking_date'];
        $schema->position = $pos['position'];
        $schema->points = $pos['points'];

        $settings = json_decode($pos['settings'], true);
        $resultIds = array_merge($settings['included'], $settings['excluded']);
        $ct = Competition::tableName();
        $results = Result::whereIn('result_id', $resultIds)
            ->with(['competition', 'competition.event', 'competition.event.country', 'competition.weapon', 'competition.category'])
            ->joinRelationship('competition')
            ->orderBy($ct . '.competition_opens', 'desc')
            ->get();
        foreach ($results as $result) {
            \Log::debug("assigning result " . json_encode($result));
            $resultSchema = new ResultSchema();
            $resultSchema->event = $result->competition->event->event_name;
            $resultSchema->year = intval($result->competition->event->event_year);
            $resultSchema->location = $result->competition->event->event_location;
            $resultSchema->country = $result->competition->event->country->country_name;
            $resultSchema->weapon = $result->competition->weapon->weapon_abbr;
            $resultSchema->category = $result->competition->category->category_abbr;
            $resultSchema->entries = $result->result_entry;
            $resultSchema->position = $result->result_place;
            $resultSchema->points = $result->result_points;
            $resultSchema->de = $result->result_de_points;
            $resultSchema->podium = $result->result_podium_points;
            $resultSchema->factor = $result->competition->event->event_factor;
            $resultSchema->total = $result->result_total_points;
            $resultSchema->status = in_array($result->getKey(), $settings['included']) ? 'Y' : 'N';
            $schema->results[] = $resultSchema;
        }
        return $schema;
    }
}
