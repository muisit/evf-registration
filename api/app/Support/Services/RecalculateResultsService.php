<?php

namespace App\Support\Services;

use App\Models\Competition;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class RecalculateResultsService
{
    public function handle(Competition $competition)
    {
        $results = $competition->results;

        if ($results && sizeof($results)) {
            $event = $competition->event;
            $factor = floatval($event->event_factor ?? 1.0);

            // error situation, but we'll correct and ignore
            if (is_nan($factor) || $factor <= 0.0001 || $factor > 10) {
                $factor = 1.0;
            }

            foreach ($results as $res) {
                $res->result_entry = count($results);
                $this->recalculateResult($res, $factor);
            }
        }
    }

    public function recalculateResult(Result $res, $factor)
    {
        $pos = intval($res->result_place);
        $total = intval($res->result_entry);
        $res->result_points = $this->calculatePositionPoints($pos, $total);
        $res->result_de_points = $this->calculateDEPoints($pos, $total);
        $res->result_podium_points = $this->calculatePodiumPoints($pos, $total);
        $res->result_total_points =  $factor * ($res->result_points + $res->result_de_points + $res->result_podium_points);
        $res->save();
    }

    public function calculateDEPoints($pos, $total)
    {
        // Points for surviving each round of DE
        $round_bonus = 10;
        $factor = 0;
        if ($pos > 0 && $total > 1) {
            $factor = ceil(log($total, 2)) - ceil(log($pos, 2));
        }
        return $factor * $round_bonus;
    }

    public function calculatePodiumPoints($pos, $total)
    {
        // Points for reaching podium
        $podium_bonus = 3 * (pow($total, 1 / 3));
        $factor = 0;
        switch ($pos) {
            case 1:
                $factor = 3;
                break;
            case 2:
                $factor = 2;
                break;
            case 3:
            case 4:
                $factor = 1;
                break;
        }
        return $factor * $podium_bonus;
    }

    public function calculatePositionPoints($pos, $total)
    {
        $max_points = 50;
        $points = 0;
        if ($pos > 0) {
            // Place factor: 1st place gets Max_points, last place (= size of entry) gets one point)
            // Intermediate places are log curve = MP - (MP-1) * log(x)/log(N)
            if (($total <= 1) && ($pos == 1)) {
                $points = $max_points;
            }
            else {
                $points = $max_points - ($max_points - 1) * log($pos) / log($total);
            }
        }
        return $points;
    }
}
