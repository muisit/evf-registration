<?php

namespace Tests\Unit\App\Jobs;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Jobs\CheckDirtyBadges;
use App\Jobs\CheckBadge;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class CheckDirtyBadgesTest extends TestCase
{
    public function fixtures()
    {
        TemplateData::create();
        FencerData::create();
        EventData::create();
        EventRoleData::create();
        SideEventData::create();
        RegistrationData::create();
        AccreditationData::create();
    }

    public function testBasicJob()
    {
        Queue::fake();
        $job = new CheckDirtyBadges();
        $job->handle();
        // nothing was dirty, so nothing is pushed
        Queue::assertNothingPushed();

        // set the dirty date to way in the past
        Accreditation::where('is_dirty', null)->update(['is_dirty' => '2000-01-01']);

        // now all accreditations are dirty, so we'll push jobs
        $job->handle();
        // there are 11 unique fencers registered for this event
        // However, for 4 cases accreditations are missing in the dataset
        // and we only have 7 active combinations
        Queue::assertPushed(CheckBadge::class, 7);
    }

    public function testUnique()
    {
        Queue::fake();
        $job = new CheckDirtyBadges();
        dispatch($job);

        $job = new CheckDirtyBadges();
        dispatch($job);

        $job = new CheckDirtyBadges();
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(CheckDirtyBadges::class, 1);
    }

    public function testRunForOldEvent()
    {
        Queue::fake();
        $job = new CheckDirtyBadges();
        $job->handle();
        // nothing was dirty, so nothing is pushed
        Queue::assertNothingPushed();

        $event = Event::find(EventData::EVENT1);
        $event->event_open = '2000-01-01';
        $event->save();
        Accreditation::where('is_dirty', null)->update(['is_dirty' => '2000-01-01']);
        $job->handle();
        // nothing pushed, event is too old
        Queue::assertNothingPushed();
    }

    public function testRunForJustClosedEvent()
    {
        Queue::fake();
        $job = new CheckDirtyBadges();
        $job->handle();
        // nothing was dirty, so nothing is pushed
        Queue::assertNothingPushed();

        $event = Event::find(EventData::EVENT1);
        $event->event_open = Carbon::now()->subDays($event->event_duration)->toDateString();
        $event->save();
        Accreditation::where('is_dirty', null)->update(['is_dirty' => '2000-01-01']);
        $job->handle();
        // nothing pushed, event is closed
        Queue::assertNothingPushed();
    }

    public function testRunForStillOpenEvent()
    {
        Queue::fake();
        $job = new CheckDirtyBadges();
        $job->handle();
        // nothing was dirty, so nothing is pushed
        Queue::assertNothingPushed();

        $event = Event::find(EventData::EVENT1);
        $event->event_open = Carbon::now()->subDays($event->event_duration - 1)->toDateString();
        $event->save();
        Accreditation::where('is_dirty', null)->update(['is_dirty' => '2000-01-01']);
        $job->handle();
        // nothing pushed, event is closed
        Queue::assertPushed(CheckBadge::class, 7);
    }

}
