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
use App\Jobs\CheckBadge;
use App\Jobs\CreateBadge;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;

class CheckBadgeTest extends TestCase
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
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        Queue::fake();
        $job = new CheckBadge(FencerData::MCAT1, EventData::EVENT1);
        $job->handle();

        // this should replace the data of the existing accreditation
        $accreditations = Accreditation::where('fencer_id', FencerData::MCAT1)->where('event_id', EventData::EVENT1)->get();
        $this->assertCount(1, $accreditations);
        $this->assertEquals(AccreditationData::MFCAT1, $accreditations[0]->getKey());
        $this->assertNotEquals($accreditation->hash, $accreditations[0]->hash);
        $this->assertEmpty($accreditations[0]->is_dirty); // the job resets the dirty value before it pushes CreateBadge

        Queue::assertPushed(CreateBadge::class, 1);

        $job = new CheckBadge(FencerData::MCAT1, EventData::EVENT1);
        $job->handle();
        // no new job
        Queue::assertPushed(CreateBadge::class, 1);

        $job = new CheckBadge(FencerData::MCAT2, EventData::EVENT1);
        $job->handle();
        Queue::assertPushed(CreateBadge::class, 2);

        $job = new CheckBadge(FencerData::MCAT5, EventData::EVENT1);
        $job->handle();
        Queue::assertPushed(CreateBadge::class, 5);
    }

    public function _testUnique()
    {
        Queue::fake();
        $job = new CheckBadge(FencerData::MCAT1, EventData::EVENT1);
        dispatch($job);

        $job = new CheckBadge(FencerData::MCAT1, EventData::EVENT1);
        dispatch($job);

        $job = new CheckBadge(FencerData::MCAT1, EventData::EVENT1);
        dispatch($job);

        // only one job actually pushed
        Queue::assertPushed(CheckBadge::class, 1);
    }
}
