<?php

namespace Tests\Unit\App\Support;

use App\Models\Event;
use App\Models\Role;
use App\Support\Services\OverviewService;
use App\Support\Traits\EVFUser;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Unit\TestCase;

class OverviewServiceTest extends TestCase
{
    public function fixtures()
    {
        RegistrationData::create();
    }

    public function testInitialise()
    {
        $service = new OverviewService(Event::where('event_id', EventData::EVENT1)->first());
        $service->initialise();

        $this->assertCount(6, $service->sidesById);
        $this->assertCount(1, $service->teamEvents);
        $this->assertCount(21, $service->roleById);
        $this->assertCount(4, $service->roleTypeById);
        $this->assertCount(0, $service->overview);
    }

    public function testAddEventRole()
    {
        $service = new OverviewService(Event::where('event_id', EventData::EVENT1)->first());
        $service->initialise();

        $this->assertCount(0, $service->overview);

        // a non-team event
        $service->addEventRole("c2", "s" . SideEventData::MFCAT1, 2, null);
        $this->assertCount(1, $service->overview);
        $this->assertCount(1, $service->overview['c2']);
        $this->assertEquals([2, 0], $service->overview['c2']['s' . SideEventData::MFCAT1]);

        // add more entries
        $service->addEventRole("c2", "s" . SideEventData::MFCAT1, 5, null);
        $this->assertCount(1, $service->overview);
        $this->assertCount(1, $service->overview['c2']);
        $this->assertEquals([7, 0], $service->overview['c2']['s' . SideEventData::MFCAT1]);

        // add a team event
        $service->addEventRole("c2", "s" . SideEventData::MFTEAM, 2, 'team1');
        $this->assertCount(1, $service->overview);
        $this->assertCount(2, $service->overview['c2']);
        $this->assertEquals([2, 1], $service->overview['c2']['s' . SideEventData::MFTEAM]);

        // add another team
        $service->addEventRole("c2", "s" . SideEventData::MFTEAM, 8, 'team1');
        $this->assertCount(1, $service->overview);
        $this->assertCount(2, $service->overview['c2']);
        $this->assertEquals([10, 2], $service->overview['c2']['s' . SideEventData::MFTEAM]);

        // bogus event
        $service->addEventRole("ladi", "dadi", 7, null);
        $this->assertCount(2, $service->overview);
        $this->assertCount(1, $service->overview['ladi']);
        $this->assertEquals([7, 0], $service->overview['ladi']['dadi']);
        $service->addEventRole("ladi", "dadi", 1, null);
        $this->assertCount(2, $service->overview);
        $this->assertCount(1, $service->overview['ladi']);
        $this->assertEquals([8, 0], $service->overview['ladi']['dadi']);

        // add a non-existing team
        $service->addEventRole("c2", "s" . SideEventData::MFTEAM, 2, 'team2');
        $this->assertCount(2, $service->overview);
        $this->assertCount(2, $service->overview['c2']);
        $this->assertEquals([12, 3], $service->overview['c2']['s' . SideEventData::MFTEAM]);

        // empty team name is not added
        $service->addEventRole("c2", "s" . SideEventData::MFTEAM, 2, null);
        $this->assertCount(2, $service->overview);
        $this->assertCount(2, $service->overview['c2']);
        $this->assertEquals([12, 3], $service->overview['c2']['s' . SideEventData::MFTEAM]);
    }

    public function testAddSupportRole()
    {
        $service = new OverviewService(Event::where('event_id', EventData::EVENT1)->first());
        $service->initialise();

        $this->assertCount(0, $service->overview);

        $service->addSupportRole(Role::HOD, "c2", 3);
        $this->assertCount(1, $service->overview);
        $this->assertCount(1, $service->overview['c2']);
        $this->assertEquals([3, 0], $service->overview['c2']['ssup']);

        $service->addSupportRole(Role::COACH, "c3", 7);
        $this->assertCount(2, $service->overview);
        $this->assertCount(1, $service->overview['c3']);
        $this->assertEquals([7, 0], $service->overview['c3']['ssup']);

        $service->addSupportRole(Role::COACH, "c2", 1);
        $this->assertCount(2, $service->overview);
        $this->assertCount(1, $service->overview['c2']);
        $this->assertEquals([4, 0], $service->overview['c2']['ssup']);

        $service->addSupportRole(Role::REFEREE, "c4", 11);
        $this->assertCount(3, $service->overview);
        $this->assertCount(1, $service->overview['corg']);
        $this->assertEquals([11, 0], $service->overview['corg']['r' . Role::REFEREE]);

        $service->addSupportRole(Role::DT, "c5", 13);
        $this->assertCount(3, $service->overview);
        $this->assertCount(2, $service->overview['corg']);
        $this->assertEquals([13, 0], $service->overview['corg']['r' . Role::DT]);

        $service->addSupportRole(Role::DIRECTOR, "c6", 19);
        $this->assertCount(4, $service->overview);
        $this->assertCount(1, $service->overview['coff']);
        $this->assertEquals([19, 0], $service->overview['coff']['r' . Role::DIRECTOR]);
    }

    public function testOverview()
    {
        $service = new OverviewService(Event::where('event_id', EventData::EVENT1)->first());
        $result = $service->create();

        $this->assertCount(6, $result);

        $this->assertCount(2, $result['c2']);
        $this->assertEquals([1, 0], $result['c2']['s2']);
        $this->assertEquals([1, 1], $result['c2']['s3']);

        $this->assertCount(1, $result['c11']);
        $this->assertEquals([1, 0], $result['c11']['s6']);

        $this->assertCount(5, $result['c12']);
        $this->assertEquals([1, 0], $result['c12']['s1']);
        $this->assertEquals([3, 1], $result['c12']['s3']);
        $this->assertEquals([1, 0], $result['c12']['s4']);
        $this->assertEquals([1, 0], $result['c12']['s5']);
        $this->assertEquals([2, 0], $result['c12']['ssup']); // Coach and Hod

        $this->assertCount(1, $result['c21']);
        $this->assertEquals([1, 0], $result['c21']['s5']);

        $this->assertCount(3, $result['corg']);
        $this->assertEquals([1, 0], $result['corg']['r7']);
        $this->assertEquals([1, 0], $result['corg']['r11']);
        $this->assertEquals([2, 0], $result['corg']['ssup']); // invitations to Gala and Cocktail

        $this->assertCount(1, $result['coff']);
        $this->assertEquals([1, 0], $result['coff']['r14']);
    }
}
