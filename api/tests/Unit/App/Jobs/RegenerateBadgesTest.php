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
use App\Jobs\RegenerateBadges;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;

class RegenerateBadgesTest extends TestCase
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
        $event = Event::find(EventData::EVENT1);
        $count = Accreditation::where('is_dirty', null)->count();
        $this->assertEquals(10, $count);
        $job = new RegenerateBadges($event);
        $job->handle();
        $count = Accreditation::where('is_dirty', null)->count();
        $this->assertEquals(0, $count);
    }

    public function testUnique()
    {
        Queue::fake();

        $event = Event::find(EventData::EVENT1);
        $job = new RegenerateBadges($event);
        dispatch($job);

        $job = new RegenerateBadges($event);
        dispatch($job);

        $job = new RegenerateBadges($event);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(RegenerateBadges::class, 1);
    }
}
