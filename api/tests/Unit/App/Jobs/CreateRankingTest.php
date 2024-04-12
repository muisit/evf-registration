<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Ranking;
use App\Jobs\CreateRanking;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class CreateRankingTest extends TestCase
{
    public function testBasicJob()
    {
        $job = new CreateRanking();
        $job->handle();

        $date = Carbon::now()->addDays(35)->toDateString();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $ranking); // mens foil, womens sabre, all categories
        $this->assertCount(3, $ranking[0]->positions);
        $this->assertEquals(EventData::EVENT1, $ranking[0]->event->getKey());
        $this->assertEquals($date, (new Carbon($ranking[0]->ranking_date))->toDateString());
    }

    public function testUnique()
    {
        Queue::fake();
        $job = new CreateRanking();
        dispatch($job);

        $job = new CreateRanking();
        dispatch($job);

        $job = new CreateRanking();
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(CreateRanking::class, 1);
    }
}
