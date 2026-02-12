<?php

namespace App\Support\Services;

use App\Models\Result;
use Illuminate\Support\Facades\DB;

class AssessResultsService
{
    public $cutoff;

    public function __construct()
    {
        $this->cutoff = BasicDataService::getCutOff();
    }

    public function handle()
    {
        Result::whereIn('result_in_ranking', ['Y', 'N'])->update(['result_in_ranking' => 'N']);

        // sort by fencer, weapon, category
        // then by points to get the best results first, then by event_open to get the most recent best results first
        $results = DB::table('VW_Ranking')
            ->select('result_id', 'fencer_id', 'weapon_id', 'result_in_ranking')
            ->orderBy('fencer_id', 'asc')
            ->orderBy('weapon_id', 'asc')
            ->orderBy('result_total_points', 'desc')
            ->orderBy('event_open', 'desc')
            ->get();

        $current_fencer = null;
        $current_weapon = null;
        $cnt = 0;
        $allresults = array();
        $totalresults = 0;
        foreach ($results as $r) {
            if ($r->result_in_ranking == 'N') {
                $fid = intval($r->fencer_id);
                $wid = intval($r->weapon_id);

                // change in fencer means a change in weapon as well
                if ($current_fencer === null || $current_fencer != $fid) {
                    $current_fencer = $fid;
                    $current_weapon = null;
                }
                // change in weapon means we start counting anew
                if ($current_weapon === null || $current_weapon != $wid) {
                    $current_weapon = $wid;
                    $cnt = 0;
                }

                if ($cnt < $this->cutoff) {
                    $allresults[] = $r->result_id;
                    $cnt += 1;
                }
                // else skip this result, it is not used for the ranking at this point
            }
            // else this is an Excluded result, or it was already taken into account (which is odd...)

            if (sizeof($allresults) > 100) {
                $totalresults += sizeof($allresults);
                Result::whereIn('result_id', $allresults)->update(['result_in_ranking' => 'Y']);
                $allresults = array();
            }
        }

        if (sizeof($allresults) > 0) {
            $totalresults += sizeof($allresults);
            Result::whereIn('result_id', $allresults)->update(['result_in_ranking' => 'Y']);
        }
        return $totalresults;
    }
}
