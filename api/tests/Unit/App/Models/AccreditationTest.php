<?php

namespace Tests\Unit\App\Models;

use App\Models\Accreditation;
use App\Models\AccreditationTemplate;
use App\Models\AccreditationUser;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Role;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class AccreditationTest extends TestCase
{
    public function testRelations()
    {
        $this->assertCount(10, Accreditation::get());
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);

        $this->assertEquals(EventData::EVENT1, $accreditation->event_id);
        $this->assertInstanceOf(BelongsTo::class, $accreditation->event());
        $this->assertInstanceOf(Event::class, $accreditation->event);
        $this->assertEquals(EventData::EVENT1, $accreditation->event->getKey());

        $this->assertEquals(TemplateData::ATHLETE, $accreditation->template_id);
        $this->assertInstanceOf(BelongsTo::class, $accreditation->template());
        $this->assertInstanceOf(AccreditationTemplate::class, $accreditation->template);
        $this->assertEquals(TemplateData::ATHLETE, $accreditation->template->getKey());

        $this->assertEquals(FencerData::MCAT1, $accreditation->fencer_id);
        $this->assertInstanceOf(BelongsTo::class, $accreditation->fencer());
        $this->assertInstanceOf(Fencer::class, $accreditation->fencer);
        $this->assertEquals(FencerData::MCAT1, $accreditation->fencer->getKey());
    }

    public function testPath()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $this->assertEquals("pdfs/event1/badges/badge_1311.pdf", $accreditation->path(false));
        $abspath = $accreditation->path(true);
        $this->assertTrue(strlen($abspath) > strlen($accreditation->path(false)));
        $this->assertTrue($abspath[0] == '/');
    }

    public function testDelete()
    {
        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $users = AccreditationUser::where('accreditation_id', $accreditation->getKey())->get();
        $path = $accreditation->path(true);
        @mkdir(dirname($path));
        file_put_contents($path, "test");
        $this->assertTrue(file_exists($path));
        $this->assertCount(1, $users);

        $accreditation->delete();
        $this->assertFalse(file_exists($path));
        $users = AccreditationUser::where('accreditation_id', $accreditation->getKey())->get();
        $this->assertCount(0, $users);
    }

    public function testCreateControlDigit()
    {
        $codes = [
            "0582230" => 0,
            "1670431" => 8,
            "2120626" => 1,
            "3239346" => 0,
            "4416112" => 1,
            "1270578" => 0,
            "1954492" => 6,
            "1349929" => 3,
            "1" => 9,
            "11" => 8,
            "20" => 8,
            "5555555555" => 0,
            "0" => 0,
            "0000" => 0,
            "11111111111" => 9
        ];
        foreach ($codes as $code => $digit) {
            $control = Accreditation::createControlDigit($code);
            $this->assertEquals($digit, $control);
        }
    }

    public function testCreateId()
    {
        $accreditation = new Accreditation();
        $id = $accreditation->createId();
        $this->assertEquals($id, $accreditation->fe_id);
        $this->assertEmpty(Accreditation::where('fe_id', $id)->get());
        $this->assertTrue(strlen($id) == 7);
        $this->assertEquals('1', $id[0]);

        $accreditation = Accreditation::find(AccreditationData::MFCAT1);
        $accreditation->createId(1); // set to 1 to force basing it on id
        $accreditation->save();

        // artificially set an id that already is present to create a collision
        $accreditation = new Accreditation();
        $accreditation->id = AccreditationData::MFCAT1;
        $id = $accreditation->createId(1);
        $this->assertTrue(strlen($id) == 7);
        $this->assertEquals('0', $id[0]);
        // this id is not checked, because id should be unique always
        $this->assertNotEmpty(Accreditation::where('fe_id', $id)->get());
    }
}
