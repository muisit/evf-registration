<?php

namespace Tests\Unit\App\Support;

use App\Models\Result;
use App\Support\Services\AssessResultsService;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Unit\TestCase;

class AssessResultsServiceTest extends TestCase
{
    public function testBasic()
    {
        $service = new AssessResultsService();
        $service->handle();
        $count = Result::where('result_in_ranking', 'N')->count();
        // every fencer has only 1 result, so all results count
        $this->assertEquals(0, $count);

        for ($i = 0; $i < 10; $i++) {
            $result = new Result();
            $result->result_fencer = FencerData::MCAT1;
            // same fencer appears several times in the same competition.... not blocked in the system
            $result->result_competition = CompetitionData::MFCAT1;
            $result->result_place = 2 + $i;
            $result->result_points = 30.2;
            $result->result_entry = 3;
            $result->result_de_points = 10;
            $result->result_podium_points = 56.9;
            $result->result_total_points = 97.1;
            $result->result_in_ranking = 'Y';
            $result->save();
        }

        $service->handle();
        $count = Result::where('result_in_ranking', 'N')->count();
        // one fencer has 11 results, 6 of which ought to be blocked now
        $this->assertEquals(6, $count);
    }

    public function testKeepExcluded()
    {
        // this is a real-world case where excluded results would be overturned during assessment
        $service = new AssessResultsService();

        for ($i = 0; $i < 10; $i++) {
            $result = new Result();
            $result->result_fencer = FencerData::MCAT1;
            // same fencer appears several times in the same competition.... not blocked in the system
            $result->result_competition = CompetitionData::MFCAT1;
            $result->result_place = 2 + $i;
            $result->result_points = 30.2 - (2 * $i);
            $result->result_entry = 3;
            $result->result_de_points = 10 - $i;
            $result->result_podium_points = 56.9 - (5 * $i);
            $result->result_total_points = 92.1 - (10 * $i);
            // exclude half of them, so entries at 1, 3, 5, 7 and 9
            $result->result_in_ranking = (($i % 2) == 1) ? 'Y' : 'E';
            $result->save();
        }
        $count = Result::where('result_in_ranking', 'N')->count();
        $this->assertEquals(0, $count);
        $count = Result::where('result_in_ranking', 'Y')->count();
        $this->assertEquals(16, $count);
        $count = Result::where('result_in_ranking', 'E')->count();
        $this->assertEquals(5, $count);
        $ids = Result::where('result_in_ranking', 'E')->select('result_id')->get()->pluck('result_id')->toArray();
        sort($ids);

        // of the 6 results of MCAT1, one is excluded
        $service->handle();
        $count = Result::where('result_in_ranking', 'N')->count();
        $this->assertEquals(1, $count);
        $count = Result::where('result_in_ranking', 'Y')->count();
        $this->assertEquals(15, $count);
        $count = Result::where('result_in_ranking', 'E')->count();
        $this->assertEquals(5, $count);

        $ids2 = Result::where('result_in_ranking', 'E')->select('result_id')->get()->pluck('result_id')->toArray();
        sort($ids2);
        $this->assertEquals($ids, $ids2);
    }
}
