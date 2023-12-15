<?php

namespace Tests\Unit\App\Support;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Fencer;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Role;
use App\Models\RoleType;
use App\Support\Services\AccreditationMatchService;
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
// 5 registrations for support roles (3x MCAT5, 2x MCAT4)
//
// there are 8 accreditations
// 1 for MCAT1, with 2 registrations (MFCAT1, MFTEAM1)
// 1 for MCAT1B, with 1 registration (MFTEAM2)
// 1 for MCAT1C, with 1 registration (MFTEAM3)
// 1 for MCAT2, with 2 registrations (MFCAT2, MFTEAM1)
// 1 for WCAT1, with 1 registration (WSCAT1)
// 2 for MCAT5, with 2 country support roles and 1 organisation support role
// 1 for MCAT4, with 2 support roles (organisation)

class AccreditationMatchServiceTest extends TestCase
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

    public function testHandle()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $event = Event::find(EventData::EVENT1);
        $athlete = AccreditationTemplate::find(TemplateData::ATHLETE);
        $country = AccreditationTemplate::find(TemplateData::COUNTRY);
        $org = AccreditationTemplate::find(TemplateData::ORG);

        $this->assertEquals(1, Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->count());
        $tmpl1 = $this->createTemplate($fencer, $athlete);
        $service = new AccreditationMatchService($fencer, $event);
        $service->handle([$tmpl1]);
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(1, $accreditations);
        $this->assertCount(1, $service->missingAccreditations);
        $this->assertCount(1, $service->foundAccreditations);
        $service->actualise();
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(1, $accreditations);
        $this->assertNotEquals(AccreditationData::MFCAT1, $accreditations[0]->getKey());

        $service = new AccreditationMatchService($fencer, $event);
        $service->handle([$tmpl1]);
        $service->actualise();
        // complete match
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(1, $accreditations);
        $this->assertCount(0, $service->missingAccreditations);
        $this->assertCount(1, $service->foundAccreditations);

        // add an Org template
        $tmpl2 = $this->createTemplate($fencer, $org);
        $service = new AccreditationMatchService($fencer, $event);
        $service->handle([$tmpl1, $tmpl2]);
        $service->actualise();
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(2, $accreditations);
        $this->assertCount(0, $service->missingAccreditations);
        $this->assertCount(2, $service->foundAccreditations);

        $tmpl3 = $this->createTemplate($fencer, $country);
        $service = new AccreditationMatchService($fencer, $event);
        $service->handle([$tmpl1, $tmpl2, $tmpl3]);
        $service->actualise();
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(3, $accreditations);
        $this->assertCount(0, $service->missingAccreditations);
        $this->assertCount(3, $service->foundAccreditations);

        $service = new AccreditationMatchService($fencer, $event);
        $service->handle([$tmpl3]);
        $this->assertCount(2, $service->missingAccreditations);
        $this->assertCount(1, $service->foundAccreditations);
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(3, $accreditations);
        $service->actualise();
        $accreditations = Accreditation::where('fencer_id', $fencer->getKey())->where('event_id', $event->getKey())->get();
        $this->assertCount(1, $accreditations);
    }

    private function createTemplate($fencer, $template)
    {
        $content = [
            'lastname' => strtoupper($fencer->fencer_surname),
            'firstname' => $fencer->fencer_firstname,
            'category' => 1,
            'organisation' => '',
            'roles' => [],
            'dates' => [],
            'country' => $fencer->country->country_abbr,
            'country_flag' => $fencer->country->country_flag_path,
            'photo_hash' => '---',
            'template_hash' => hash('sha256', $template->content, false)
        ];
        return ["template" => $template, "content" => $content];
    }
}
