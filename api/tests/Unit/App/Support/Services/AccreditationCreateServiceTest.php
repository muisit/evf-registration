<?php

namespace Tests\Unit\App\Support;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Competition;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Fencer;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Models\SideEvent;
use App\Support\Services\AccreditationCreateService;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

// there are 17 registrations
// 1 registration for MFCAT1 (id 1)
// 1 registration for MFCAT2 (id 2)
// 1 registration for WSCAT1 (id 3)
// 4 registrations for MFTEAM (id 4)
// 3 registrations for the cocktail dinatoire (id 5, MCAT1, MCAT5, WCAT4)
// 2 registrations for the gala (id 6, WCAT3, MCAT3)
// 5 registrations for support roles (3x MCAT5, 2x MCAT4)
//
// there are 9 accreditations
// 1 for MCAT1, with 2 registrations (MFCAT1, MFTEAM1)
// 1 for MCAT1B, with 1 registration (MFTEAM2)
// 1 for MCAT1C, with 1 registration (MFTEAM3)
// 1 for MCAT2, with 2 registrations (MFCAT2, MFTEAM1)
// 1 for WCAT1, with 1 registration (WSCAT1)
// 2 for MCAT5, with 2 country support roles and 2 organisation support roles for 2 different templates
// 1 for MCAT4, with 2 support roles (organisation)

class AccreditationCreateServiceTest extends TestCase
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

    public function testAthlete()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $event = Event::find(EventData::EVENT1);
        $competition = Competition::find(CompetitionData::MFCAT1);
        $competition->competition_weapon_check = '2020-01-01';
        $competition->save();
        $competition = Competition::find(CompetitionData::MFTEAM);
        $competition->competition_weapon_check = '2020-01-02';
        $competition->save();
        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->handle();
        // MCAT1 has 1 registration for MFCAT1, 1 for MFTEAM1, 1 for the cocktail, but that does not give an accreditation
        $this->assertCount(1, $output); // only one entry
        $this->assertEquals(TemplateData::ATHLETE, $output[0]['template']->getKey());
        $this->assertEquals(1, $output[0]['content']['category']);
        $this->assertEquals(['MF', 'MF1'], $output[0]['content']['roles']); // one Team, one Individual
        $this->assertEquals(['1 WED', '2 THU'], $output[0]['content']['dates']);
        $this->assertEquals(strtoupper($fencer->fencer_surname), $output[0]['content']['lastname']);
        $this->assertEquals($fencer->fencer_firstname, $output[0]['content']['firstname']);
        $this->assertEquals('', $output[0]['content']['organisation']); // empty for athletes
        $this->assertEquals('GER', $output[0]['content']['country']);
        $this->assertEquals('---', $output[0]['content']['photo_hash']);
        $this->assertNotEmpty($output[0]['content']['template_hash']);
    }

    public function testOfficial()
    {
        $fencer = Fencer::find(FencerData::MCAT5);
        $event = Event::find(EventData::EVENT1);
        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->handle();
        // MCAT5 has 4 official roles, 2 federative, one referee and one organisational
        $this->assertCount(3, $output); // one federative, one organisational, one referee badge

        $this->assertEquals(TemplateData::COUNTRY, $output[1]['template']->getKey());
        $this->assertEquals(4, $output[1]['content']['category']); // 4 is the max
        $this->assertEquals(['Coach', 'Head of Delegation'], $output[1]['content']['roles']); // one Team, one Individual
        $this->assertEquals(['ALL'], $output[1]['content']['dates']);
        $this->assertEquals('GER', $output[1]['content']['organisation']);
        $this->assertEquals('GER', $output[1]['content']['country']);

        $this->assertEquals(TemplateData::ORG, $output[0]['template']->getKey());
        $this->assertEquals(4, $output[0]['content']['category']);
        $this->assertEquals(['Volunteer'], $output[0]['content']['roles']);
        $this->assertEquals(['ALL'], $output[0]['content']['dates']);
        $this->assertEquals('ORG', $output[0]['content']['organisation']);
        $this->assertEquals('GER', $output[0]['content']['country']);

        $this->assertEquals(TemplateData::REFEREE, $output[2]['template']->getKey());
        $this->assertEquals(4, $output[2]['content']['category']);
        $this->assertEquals(['Referee'], $output[2]['content']['roles']);
        $this->assertEquals(['ALL'], $output[2]['content']['dates']);
        $this->assertEquals('ORG', $output[2]['content']['organisation']);
        $this->assertEquals('GER', $output[2]['content']['country']);
    }

    public function testOnAllDates()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $event = Event::find(EventData::EVENT1);
        SideEvent::where('event_id', EventData::EVENT1)->delete();
        Registration::where('registration_mainevent', EventData::EVENT1)->delete();

        $competition1 = Competition::find(CompetitionData::MFCAT1);
        $competition1->competition_weapon_check = '2020-01-01';
        $competition1->save();
        $competition2 = Competition::find(CompetitionData::MFCAT2);
        $competition2->competition_weapon_check = '2020-01-02';
        $competition2->save();
        $competition3 = Competition::find(CompetitionData::WSCAT1);
        $competition3->competition_weapon_check = '2020-01-03';
        $competition3->save();

        $se1 = new SideEvent();
        $se1->competition_id = $competition1->getKey();
        $se1->event_id = EventData::EVENT1;
        $se1->title = "Title 1";
        $se1->description = '';
        $se1->costs = 0.0;
        $se1->starts = $competition1->competition_weapon_check;
        $se1->save();

        $se2 = new SideEvent();
        $se2->competition_id = $competition2->getKey();
        $se2->event_id = EventData::EVENT1;
        $se2->title = "Title 1";
        $se2->description = '';
        $se2->costs = 0.0;
        $se2->starts = $competition2->competition_weapon_check;
        $se2->save();

        $se3 = new SideEvent();
        $se3->competition_id = $competition3->getKey();
        $se3->event_id = EventData::EVENT1;
        $se3->title = "Title 1";
        $se3->description = '';
        $se3->costs = 0.0;
        $se3->starts = $competition3->competition_weapon_check;
        $se3->save();

        $reg1 = $this->createRegistration($se1->getKey(), 0);
        $reg1->save();

        $reg2 = $this->createRegistration($se2->getKey(), 0);
        $reg2->save();

        $reg3 = $this->createRegistration($se3->getKey(), 0);
        $reg3->save();

        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->handle();

        $this->assertCount(1, $output); // only one entry
        $this->assertEquals(TemplateData::ATHLETE, $output[0]['template']->getKey());
        $this->assertEquals(1, $output[0]['content']['category']);
        $this->assertEquals(['MF1', 'MF2', 'WS1'], $output[0]['content']['roles']);
        // This test specifically tests that we have a registration for events covering all dates
        $this->assertEquals(['ALL'], $output[0]['content']['dates']);
        // secondary test: registration country is different from fencer country
        $this->assertEquals('ITA', $output[0]['content']['country']);
    }

    public function testCheckRolesAndDates()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $event = Event::find(EventData::EVENT1);
        $opens = Carbon::now()->addDays(11)->toDateString();
        $check = Carbon::now()->addDays(10)->toDateString();

        $reg1 = $this->createRegistration(SideEventData::MFCAT1, 0);
        $reg2 = $this->createRegistration(SideEventData::MFCAT2, 0);
        $reg3 = $this->createRegistration(SideEventData::MFTEAM, 0);
        $reg4 = $this->createRegistration(SideEventData::WSCAT1, 0);
        $reg5 = $this->createRegistration(SideEventData::DINATOIRE, 0);
        $reg6 = $this->createRegistration(SideEventData::GALA, 0);
        $reg7 = $this->createRegistration(null, Role::HOD);
        $reg8 = $this->createRegistration(null, Role::COACH);
        $reg9 = $this->createRegistration(null, Role::REFEREE);
        $reg10 = $this->createRegistration(null, Role::VOLUNTEER);
        $reg11 = $this->createRegistration(null, Role::DT);
        $reg12 = $this->createRegistration(null, Role::DIRECTOR);

        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->checkRolesAndDates(collect([$reg1]));
        $this->assertCount(1, $output[$check]["registrations"]);
        $this->assertCount(1, $output[$check]["roles"]);
        $this->assertCount(0, $output[$check]["sideevents"]); // the side event has not started yet on this date
        $this->assertEmpty($output[$opens]['registrations']);
        $this->assertEmpty($output[$opens]['roles']);
        $this->assertCount(1, $output[$opens]['sideevents']);
        $this->assertEmpty($output['all']['registrations']);
        $this->assertEmpty($output['all']['roles']);
        $this->assertEmpty($output['all']['sideevents']);

        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->checkRolesAndDates(collect([$reg1, $reg2, $reg7])); // one all role
        $this->assertCount(2, $output[$check]["registrations"]);
        $this->assertCount(2, $output[$check]["roles"]);
        $this->assertCount(1, $output['all']['registrations']);
        $this->assertCount(1, $output['all']['roles']);
        $this->assertEmpty($output['all']['sideevents']);

        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->checkRolesAndDates(collect([$reg1, $reg2, $reg5, $reg6])); // cocktail and gala are discarded
        $this->assertCount(2, $output[$check]["registrations"]);
        $this->assertCount(2, $output[$check]["roles"]);
        $this->assertEmpty($output['all']['registrations']);
        $this->assertEmpty($output['all']['roles']);
        $this->assertEmpty($output['all']['sideevents']);

        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->checkRolesAndDates(collect([$reg7, $reg8, $reg9, $reg10, $reg11, $reg12])); // all all roles
        $this->assertCount(2, array_keys($output));
        $this->assertCount(0, $output[$opens]["registrations"]);
        $this->assertCount(0, $output[$opens]["roles"]);
        $this->assertCount(6, $output['all']['registrations']);
        $this->assertCount(6, $output['all']['roles']);
        $this->assertEmpty($output['all']['sideevents']);

        // event-related roles
        $reg7->registration_event = SideEventData::MFCAT1;
        $service = new AccreditationCreateService($fencer, $event);
        $output = $service->checkRolesAndDates(collect([$reg7]));
        $this->assertCount(2, array_keys($output));
        $this->assertCount(1, $output[$opens]["registrations"]);
        $this->assertCount(1, $output[$opens]["roles"]);
        $this->assertCount(0, $output['all']['registrations']);
        $this->assertCount(0, $output['all']['roles']);
        $this->assertEmpty($output['all']['sideevents']);
    }

    private function createRegistration(?int $sideId, int $roleId)
    {
        $reg = new Registration();
        $reg->registration_fencer = FencerData::MCAT1;
        $reg->registration_mainevent = EventData::EVENT1;
        $reg->registration_event = $sideId;
        $reg->registration_role = $roleId;
        $reg->registration_country = Country::ITA;
        $reg->registration_date = ' 2020-01-01';
        return $reg;
    }
}
