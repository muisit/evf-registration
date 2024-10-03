<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Competition;
use App\Models\Fencer;
use App\Events\RegisterForEvent;
use App\Jobs\RegistrationFeedEvents;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class RegistrationFeedEventsTest extends TestCase
{
    public function testBasicJob()
    {
        Event::fake(); // do not execute listeners
        $f = Fencer::find(FencerData::MCAT1);
        $c = Competition::find(CompetitionData::MFCAT1);

        $job = new RegistrationFeedEvents($f, $c, false);
        $job->handle();

        Event::assertDispatched(RegisterForEvent::class, 1);
    }

    public function testUnique()
    {
        Queue::fake();
        $f = Fencer::find(FencerData::MCAT1);
        $c = Competition::find(CompetitionData::MFCAT1);
        $job = new RegistrationFeedEvents($f, $c, false);
        dispatch($job);

        $job = new RegistrationFeedEvents($f, $c, false);
        dispatch($job);

        $job = new RegistrationFeedEvents($f, $c, false);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(RegistrationFeedEvents::class, 1);

        $job = new RegistrationFeedEvents($f, $c, true);
        dispatch($job);
        Queue::assertPushed(RegistrationFeedEvents::class, 2);
    }
}
