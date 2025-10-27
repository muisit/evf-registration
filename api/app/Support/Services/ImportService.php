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
    public $ranking;
    public $resultService;
    public $labelService;

    public function handle(Competition $competition, $import)
    {
        $this->messages = [];
        $this->competition = $competition;
        $this->factor = floatval($this->competition->event->event_factor);
        // error situation, but we'll correct and ignore
        if ($this->factor <= 0.0000001) {
            $this->factor = 1.0;
        }

        $this->resultService = new RecalculateResultsService();
        $this->labelService = new FencerLabelService();

        if ($this->checkImport($import['ranking'] ?? [])) {
            return $this->doImport();
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
        $this->ranking = [];
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
                    $this->messages[] = "Invalid position " . $position['pos'] . " after $start for fencer " . $fencer->fencer_surname . '. ' . $fencer->fencer_firstname;
                }
                else {
                    $start = intval($position['pos']);
                }
            }
            $position['fencer'] = $fencer;
            $this->ranking[] = $position;
        }
        return count($this->messages) == 0;
    }

    public function doImport()
    {
        $this->clearResultsOfCompetition();
        $totalEntries = count($this->ranking);
        // import consists of a ranking containing: fencer_id, name, firstname and pos
        foreach ($this->ranking as $position) {
            $this->import($position, $totalEntries);
        }
        return new WPResponse(['status' => 'ok']);
    }

    public function import($position, $total)
    {
        $result = new Result();
        $result->result_competition = $this->competition->getKey();
        $result->result_fencer = $position['fencer']->getKey();
        $result->result_place = $position['pos'];
        $result->result_entry = $total;

        $this->resultService->recalculateResult($result, $this->factor);

        // finally, see if the firstname and lastname of this fencer are already in the list of labels for that fencer
        // If not, we add them, so we have a better chance of matching next time
        \Log::debug("fencer " . $position['fencer']->fencer_surname . ', ' . $position['fencer']->fencer_firstname . ': labels ' . $position['name'] . ', ' . $position['firstname']);
        $this->labelService->extendFencer($position['fencer'], $position['firstname'], $position['name']);
    }

    private function clearResultsOfCompetition()
    {
        $this->competition->results()->delete();
    }
}
