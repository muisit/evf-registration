<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Ranking;
use App\Events\RankingUpdate;
use App\Jobs\RankingFeedEvents;
use App\Support\Services\RankingStoreService;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class RankingFeedEventsTest extends TestCase
{
    public function testBasicJob()
    {
        Event::fake(); // do not execute listeners

        $service = new RankingStoreService();
        $service->handle();
        $rankings = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();

        $job = new RankingFeedEvents($rankings[0]);
        $job->handle();

        Event::assertDispatched(RankingUpdate::class, 3);
    }

    public function testUnique()
    {
        Queue::fake();
        $ranking = new Ranking();
        $job = new RankingFeedEvents($ranking);
        dispatch($job);

        $job = new RankingFeedEvents($ranking);
        dispatch($job);

        $job = new RankingFeedEvents($ranking);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(RankingFeedEvents::class, 1);
    }
}
