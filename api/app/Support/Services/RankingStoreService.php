<?php

namespace App\Support\Services;

use App\Models\Category;
use App\Models\Event;
use App\Models\Ranking;
use App\Models\RankingPosition;
use App\Models\Weapon;
use App\Jobs\RankingFeedEvents;
use Illuminate\Support\Facades\DB;

class RankingStoreService
{
    private $fencerWeaponCache = [];

    public function handle()
    {
        $weapons = Weapon::where('weapon_id', '>', 0)->get();
        // only create rankings for the Individual categories, and for age groups 1, 2, 3 and 4
        $categories = Category::where('category_type', 'I')->whereIn('category_value', [1,2,3,4])->get();

        $this->buildCache();

        foreach ($weapons as $weapon) {
            foreach ($categories as $category) {
                $event = $this->findMostRecentEvent($weapon);
                if (!empty($event)) {
                    $ranking = $this->findOrCreateEventRanking($event, $category, $weapon);
                    // clear the current ranking
                    RankingPosition::where('ranking_id', $ranking->getKey())->delete();

                    $service = new RankingService($category, $weapon);
                    $positions = $service->generate();
                    $this->storePositionsOnRanking($ranking, $weapon, $positions);
                    dispatch(new RankingFeedEvents($ranking));
                }
            }
        }
    }

    private function storePositionsOnRanking(Ranking $ranking, Weapon $weapon, array $positions)
    {
        $storePositions = [];
        foreach ($positions as $position) {
            $settings = [];
            if (isset($this->fencerWeaponCache['f' . $position['id']]['w' . $weapon->weapon_id])) {
                $settings = $this->fencerWeaponCache['f' . $position['id']]['w' . $weapon->weapon_id];
            }
            $storePositions[] = [
                'fencer_id' => $position['id'],
                'ranking_id' => $ranking->getKey(),
                'position' => $position['pos'],
                'points' => $position['points'],
                'settings' => json_encode($settings)
            ];
        }
        RankingPosition::insert($storePositions);
    }

    private function findOrCreateEventRanking(Event $event, Category $category, Weapon $weapon)
    {
        $ranking = Ranking::where('event_id', $event->getKey())->where('category_id', $category->getKey())->where('weapon_id', $weapon->getKey())->first();
        if (empty($ranking)) {
            $ranking = new Ranking();
            $ranking->event_id = $event->getKey();
            $ranking->category_id = $category->getKey();
            $ranking->weapon_id = $weapon->getKey();
            $ranking->ranking_date = (new \DateTimeImmutable($event->event_open))->add(new \DateInterval("P" . ($event->event_duration + 1) . "D"))->format('Y-m-d');
            $ranking->save();
        }
        return $ranking;
    }

    private function findMostRecentEvent(Weapon $weapon)
    {
        // get the most recent event with results in the database. Assume this is the event for which we
        // generate a ranking
        $row = DB::table('VW_Ranking')
            ->select(["event_id", "event_open"])
            ->where('result_in_ranking', 'Y')
            ->where('weapon_id', $weapon->getKey())
            ->orderBy('event_open', 'desc')
            ->first();
        return Event::find($row->event_id ?? -1);
    }

    private function buildCache()
    {
        // build a cache of all the results that count for each fencer by weapon
        $rows = DB::table('VW_Ranking')
            ->select(["fencer_id", "weapon_id", "result_id", "result_in_ranking"])
            ->where('fencer_country_registered', 'Y')
            ->get();
        $this->fencerWeaponCache = [];
        foreach ($rows as $row) {
            $fid = 'f' . $row->fencer_id;
            $wid = 'w' . $row->weapon_id;
            if (!isset($this->fencerWeaponCache[$fid])) {
                $this->fencerWeaponCache[$fid] = [];
            }
            if (!isset($this->fencerWeaponCache[$fid][$wid])) {
                $this->fencerWeaponCache[$fid][$wid] = ['included' => [], 'excluded' => []];
            }
            if ($row->result_in_ranking == 'Y') {
                $this->fencerWeaponCache[$fid][$wid]['included'][] = $row->result_id;
            }
            else {
                $this->fencerWeaponCache[$fid][$wid]['excluded'][] = $row->result_id;
            }
        }
    }
}