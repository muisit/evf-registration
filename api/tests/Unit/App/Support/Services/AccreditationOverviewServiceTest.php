<?php

namespace Tests\Unit\App\Support;

use App\Models\Accreditation;
use App\Models\AccreditationDocument;
use App\Models\AccreditationUser;
use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Support\Services\AccreditationOverviewService;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;

// there are 17 registrations
// 1 registration for MFCAT1 (id 1)
// 1 registration for MFCAT2 (id 2)
// 1 registration for WSCAT1 (id 3)
// 4 registrations for MFTEAM (id 4)
// 3 registrations for the cocktail dinatoire (id 5, MCAT1, MCAT5, WCAT4)
// 2 registrations for the gala (id 6, WCAT3, MCAT3)
// 6 registrations for support roles (3x MCAT5, 2x MCAT4, 1x WCAT5)
//
// there are 9 accreditations
// 1 for MCAT1, with 2 registrations (MFCAT1, MFTEAM1)
// 1 for MCAT1B, with 1 registration (MFTEAM2)
// 1 for MCAT1C, with 1 registration (MFTEAM3)
// 1 for MCAT2, with 2 registrations (MFCAT2, MFTEAM1)
// 1 for WCAT1, with 1 registration (WSCAT1)
// 2 for MCAT5, with 2 country support roles and 1 organisation support role
// 1 for MCAT4, with 2 support roles (organisation)
// 1 for WCAT5, with 1 country support role

class AccreditationOverviewServiceTest extends TestCase
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

    public function testInitialise()
    {
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);
        $this->assertEquals($service->event->getKey(), $event->getKey());
        $service->initialise();
        $this->assertCount(4, $service->sideEventIds);

        $allRoles = Role::get();
        $fedRoles = $allRoles->filter(fn ($role) => $role->role_type == RoleType::COUNTRY);
        $orgRoles = $allRoles->filter(fn ($role) => $role->role_type == RoleType::ORG);
        $evfRoles = $allRoles->filter(fn ($role) => $role->role_type == RoleType::EVF);
        $this->assertCount($allRoles->count(), array_keys($service->roleById));
        $this->assertCount(3, array_keys($service->roleByType));
        $this->assertCount($fedRoles->count(), $service->roleByType['r' . RoleType::COUNTRY]);
        $this->assertCount($orgRoles->count(), $service->roleByType['r' . RoleType::ORG]);
        $this->assertCount($evfRoles->count(), $service->roleByType['r' . RoleType::EVF]);

        $templates = AccreditationTemplate::get();
        $this->assertNotEmpty($service->templatesByRole['r0']);
        $this->assertCount(1, $service->templatesByRole['r0']);
        $this->assertEquals($service->templatesByRole['r0'][0], TemplateData::ATHLETE);

        $this->assertNotEmpty($service->templatesByRole['r' . RoleType::COUNTRY]);
        $this->assertCount(1, $service->templatesByRole['r' . RoleType::COUNTRY]);
        $this->assertEquals($service->templatesByRole['r' . RoleType::COUNTRY][0], TemplateData::COUNTRY);

        $this->assertNotEmpty($service->templatesByRole['r' . RoleType::ORG]);
        $this->assertCount(2, $service->templatesByRole['r' . RoleType::ORG]); // one ORG, one Referee
        $this->assertEquals($service->templatesByRole['r' . RoleType::ORG][0], TemplateData::ORG);

        // EVF template is the same
        $this->assertNotEmpty($service->templatesByRole['r' . RoleType::EVF]);
        $this->assertCount(1, $service->templatesByRole['r' . RoleType::EVF]);
        $this->assertEquals($service->templatesByRole['r' . RoleType::EVF][0], TemplateData::ORG);
    }
    
    public function testHumanFilesize()
    {
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);

        $this->assertEquals("0.0B", $service->humanFilesize(0));
        $this->assertEquals("-1.0B", $service->humanFilesize(-1));
        $this->assertEquals("1.0B", $service->humanFilesize(1));
        $this->assertEquals("1.0K", $service->humanFilesize(1024));
        $this->assertEquals("1.0M", $service->humanFilesize((1024 * 1024)));
        $this->assertEquals("1.0G", $service->humanFilesize((1024 * 1024 * 1024)));
        $this->assertEquals("1.0T", $service->humanFilesize((1024 * 1024 * 1024 * 1024)));
        $this->assertEquals("1.0P", $service->humanFilesize((1024 * 1024 * 1024 * 1024 * 1024)));

        $this->assertEquals("9.6G", $service->humanFilesize(10323256792));
        $this->assertEquals("9.6G", $service->humanFilesize(10323256792, 1));
        $this->assertEquals("10G", $service->humanFilesize(10323256792, 0));
        $this->assertEquals("9.61G", $service->humanFilesize(10323256792, 2));
        $this->assertEquals("9.614G", $service->humanFilesize(10323256792, 3));
        $this->assertEquals("9.6143G", $service->humanFilesize(10323256792, 4));
        $this->assertEquals("9.61428G", $service->humanFilesize(10323256792, 5));
        $this->assertEquals("9.614282G", $service->humanFilesize(10323256792, 6));
        $this->assertEquals("9.6142821G", $service->humanFilesize(10323256792, 7));
        $this->assertEquals("9.61428209G", $service->humanFilesize(10323256792, 8));
        $this->assertEquals("9.614282094G", $service->humanFilesize(10323256792, 9));
        $this->assertEquals("9.6142820939G", $service->humanFilesize(10323256792, 10));
        $this->assertEquals("9.61428209394G", $service->humanFilesize(10323256792, 11));
    }

    public function testCreateOverviewForEvents()
    {
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);
        $service->initialise();

        // we expect events MFCAT1, MFCAT2, MFTEAM and WSCAT1
        // MFCAT1 has 1 accreditation, 1 registration
        // MFCAT2 has 1 accreditation, 1 registration
        // MFTEAM has 4 accreditations, 4 registrations
        // WSCAT1 has 1 accreditation, 1 registration
        // all accreditations are clean 
        $this->assertEquals(
            '[["E",1,[1,1,0,1],[]],["E",2,[1,1,0,1],[]],["E",3,[4,4,0,4],[]],["E",4,[1,1,0,1],[]]]',
            json_encode($service->createOverviewForEvents())
        );
    }

    public function testCreateOverviewForCountries()
    {
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);
        $service->initialise();
        // the fencers are from GER (12) and ITA (2)
        // MCAT2 is from ITA and has 2 registrations (MCAT2, MFTEAM) and 1 accreditation (athlete)
        // MCAT1 (2/1), WCAT1 (1/1), MCAT1B (1/1), MCAT1C (1/1), MCAT5 (2/1 -> Coach), WCAT5 (1/1 -> HoD) => (7/6)
        $this->assertEquals(
            '[["C",2,[2,1,0,1],[]],["C",12,[7,6,0,6],[]]]',
            json_encode($service->createOverviewForCountries())
        );
    }

    public function testCreateOverviewForRoles()
    {
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);
        $service->initialise();
        // Only show non-athlete roles (which excludes registrations for non-competitive side events)
        // There are 5 registrations for support roles (3x MCAT5, 2x MCAT4)
        // Role 2 HOD is for MCAT5 (1 reg, 1 related accreditation)
        // Role 4 COACH is also for MCAT 5
        // Role 7 Referee is also for MCAT 5
        // Role 14 Director is for MCAT4
        // Role 11 Volunteer also for MCAT4
        $this->assertEquals(
            '[["R",2,[1,1,0,1],[]],["R",4,[1,1,0,1],[]],["R",7,[1,1,0,1],[]],["R",14,[1,1,0,1],[]],["R",11,[2,2,0,2],[]]]',
            json_encode($service->createOverviewForRoles())
        );
    }

    public function testCreateOverviewForTemplates()
    {
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);
        $service->initialise();
        // there are 18 registrations
        // 7 are for athlete, competition events (template 1), with 5 different accreditations, filtered out
        // 5 for non-accreditation events (not visible)
        // 2 for country support roles (template 3), with 2 accreditations (MCAT5, WCAT5)
        // 3 for organisation support roles (template 2), with 2 accreditations (MCAT5, MCAT4)
        // 1 for organisation referee role (template 4), with 1 accreditation (MCAT5)
        //
        // Because we cannot just link the registrations to accreditations at the moment,
        // we get double registrations for the support roles, which make the numbers meaningless
        // template 2 also includes the 2 template 3 registrations of MCAT5
        // template 3 also includes the template 2 registration of MCAT5 and WCAT5
        $this->assertEquals(
            '[["T",2,[6,2,0,2],[]],["T",3,[4,2,0,2],[]],["T",4,[4,1,0,1],[]]]',
            json_encode($service->createOverviewForTemplates())
        );
    }

    public function testCreateForEmpty()
    {
        // This is a test to see if there are issues when a new event is created,
        // where no registrations and accreditations (or templates) are available
        // This makes sure expected roles like Athlete have a relevant default
        // and do not throw an error
        Registration::where('registration_id', '>', 0)->delete();
        AccreditationUser::where('id', '>', 0)->delete();
        AccreditationDocument::where('id', '>', 0)->delete();
        Accreditation::where('id', '>', 0)->delete();
        $event = Event::find(EventData::EVENT1);
        $this->assertNotEmpty($event);
        $service = new AccreditationOverviewService($event);
        $output = $service->create();
        $this->assertEquals(
            '[["T",2,[0,0,0,0],[]],["T",3,[0,0,0,0],[]],["T",4,[0,0,0,0],[]]]',
            json_encode($service->createOverviewForTemplates())
        );

        AccreditationTemplate::where('id', '>', 0)->delete();
        $service = new AccreditationOverviewService($event);
        $output = $service->create();
        $this->assertEquals(
            '[]',
            json_encode($service->createOverviewForTemplates())
        );
    }
}
