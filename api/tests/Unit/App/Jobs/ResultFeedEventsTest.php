<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Competition;
use App\Events\ResultUpdate;
use App\Jobs\ResultFeedEvents;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class ResultFeedEventsTest extends TestCase
{
    public function testBasicJob()
    {
        Event::fake(); // do not execute listeners
        $c = Competition::find(CompetitionData::MFCAT1);

        $job = new ResultFeedEvents($c);
        $job->handle();

        Event::assertDispatched(ResultUpdate::class, 1);
    }

    public function testUnique()
    {
        Queue::fake();
        $c = Competition::find(CompetitionData::MFCAT1);
        $job = new ResultFeedEvents($c);
        dispatch($job);

        $job = new ResultFeedEvents($c);
        dispatch($job);

        $job = new ResultFeedEvents($c);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(ResultFeedEvents::class, 1);
    }
}
