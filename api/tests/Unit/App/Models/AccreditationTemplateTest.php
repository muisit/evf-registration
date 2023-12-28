<?php

namespace Tests\Unit\App\Models;

use App\Models\AccreditationTemplate;
use App\Models\Event;
use App\Models\Role;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\Event as EventData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class AccreditationTemplateTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
        AccreditationData::create();
    }

    public function testRelations()
    {
        $this->assertCount(4, AccreditationTemplate::get());
        $template = AccreditationTemplate::find(TemplateData::ATHLETE);
        $this->assertEquals(EventData::EVENT1, $template->event_id);
        $this->assertCount(5, $template->accreditations);
        $this->assertCount(1, $template->forRoles());
    }

    public function testImagePath()
    {
        $template = AccreditationTemplate::find(TemplateData::ATHLETE);
        $this->assertEquals(storage_path('app/templates/img_' . TemplateData::ATHLETE . '_test.pdf'), $template->image('test', 'pdf'));
        $this->assertEquals(storage_path('app/templates/img_' . TemplateData::ATHLETE . '_test.jpg'), $template->image('test', null));
    }

    public function testByRoleId()
    {
        $result = AccreditationTemplate::byRoleId(Event::find(EventData::EVENT1));
        $this->assertEquals(
            [
                'r0' => [TemplateData::ATHLETE],
                'r1' => [TemplateData::COUNTRY],
                'r2' => [TemplateData::ORG, TemplateData::REFEREE],
                'r3' => [TemplateData::ORG]
            ],
            $result
        );
    }

    public function testParseForRole()
    {
        $event = Event::find(EventData::EVENT1);
        $result = AccreditationTemplate::parseForRole($event, Role::find(Role::COACH));
        $this->assertEquals(TemplateData::COUNTRY, $result?->getKey());

        $result = AccreditationTemplate::parseForRole($event, Role::find(Role::REFEREE));
        $this->assertEquals(TemplateData::REFEREE, $result?->getKey());

        $result = AccreditationTemplate::parseForRole($event, Role::find(Role::HOD));
        $this->assertEquals(TemplateData::COUNTRY, $result?->getKey());

        $result = AccreditationTemplate::parseForRole($event, Role::find(Role::MEDICAL));
        $this->assertEquals(TemplateData::ORG, $result?->getKey());

        $athlete = new Role();
        $athlete->role_id = 0;
        $result = AccreditationTemplate::parseForRole($event, $athlete);
        $this->assertEquals(TemplateData::ATHLETE, $result?->getKey());
    }

    public function testAccreditationRelation()
    {
        $event = Event::find(EventData::EVENT1);
        $template = AccreditationTemplate::find(TemplateData::ATHLETE);
        $accreditations = $template->selectAccreditations($event);
        $this->assertCount(5, $accreditations);

        $template = AccreditationTemplate::find(TemplateData::ORG);
        $accreditations = $template->selectAccreditations($event);
        $this->assertCount(2, $accreditations);

        $template = AccreditationTemplate::find(TemplateData::COUNTRY);
        $accreditations = $template->selectAccreditations($event);
        $this->assertCount(1, $accreditations);

        $template = AccreditationTemplate::find(TemplateData::REFEREE);
        $accreditations = $template->selectAccreditations($event);
        $this->assertCount(1, $accreditations);
    }
}
