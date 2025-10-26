<?php

namespace App\Support\Services;

use App\Models\Competition;
use App\Models\Fencer;
use App\Models\Result;
use App\Models\Schemas\FE\WPResponse;

class ImportService
{
    public $factor;
    public $competition;
    public $messages;

    public function handle(Competition $competition, $import)
    {
        $this->messages = [];
        $this->competition = $competition;
        $this->factor = floatval($this->competition->event->event_factor);
        // error situation, but we'll correct and ignore
        if ($this->factor <= 0.0000001) {
            $this->factor = 1.0;
        }

        if ($this->checkImport($import['ranking'] ?? [])) {
            return $this->doImport($import['ranking'] ?? []);
        }
        else {
            $response = new WPResponse([]);
            $response->success = false;
            $response->data = ['messages' => $this->messages];
            return $response;
        }
    }

    public function checkImport($ranking)
    {
        if (empty($ranking) || count($ranking) == 0) {
            $this->messages[] = 'No ranking found';
            return false;
        }

        $start = 0;
        foreach ($ranking as $position) {
            $fencer = Fencer::find($position['fencer_id']);
            if (empty($fencer)) {
                $this->messages[] = 'Fencer ' . $position['fencer_id'] . ' (' . $position['name'] . ', ' . $position['firstname'] . ') not found';
            }
            else {
                if (intval($position['pos']) < $start) {
                    $this->messages[] = "Invalid position " . $position['pos'] . " after $start for fencer " . $fencer->fencer_surname . '. ' . $fencer->firstname;
                }
                else {
                    $start = intval($position['pos']);
                }
            }
        }
        return count($this->messages) == 0;
    }

    public function doImport($ranking)
    {
        $this->clearResultsOfCompetition();
        $totalEntries = count($ranking);
        $service = new RecalculateResultsService();

        // import consists of a ranking containing: fencer_id, name, firstname and pos
        foreach ($ranking as $position) {
            $this->import($position['pos'], $position['fencer_id'], $position['firstname'], $position['name'], $totalEntries, $service);
        }
        return new WPResponse(['status' => 'ok']);
    }

    public function import($pos, $id, $fname, $lname, $total, $service)
    {
        $result = new Result();
        $result->result_competition = $this->competition->getKey();
        $result->result_fencer = $id;
        $result->result_place = $pos;
        $result->result_entry = $total;

        $service->recalculateResult($result, $this->factor);
    }

    private function clearResultsOfCompetition()
    {
        $this->competition->results()->delete();
    }
}
