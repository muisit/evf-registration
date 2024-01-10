<?php

namespace Tests\Unit\App\Support;

use App\Models\SideEvent;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Fencer;
use App\Support\Services\RegistrationXMLService;
use Tests\Support\Data\SideEvent as EventData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Unit\TestCase;

class RegistrationXMLServiceTest extends TestCase
{
    public function testGenerate()
    {
        $se = SideEvent::find(EventData::MFCAT1);
        $registrations = Registration::where('registration_event', EventData::MFCAT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();
        $service = new RegistrationXMLService();
        $output = $service->generate($se, $registrations);
        $xml = simplexml_load_string($output);
        $this->assertCount(1, $xml);
        $this->assertEquals("BaseCompetitionIndividuelle", $xml->getName());
        $this->assertEquals(1, $xml->count());
        $this->assertEquals(1, count($xml));
        $child = $xml->children()[0];
        $this->assertEquals("Tireurs", $child->getName());
        $this->assertCount(1, $child->children());
        $fencer = $child->children()[0];
        $this->assertEquals("Tireur", $fencer->getName());
        $this->assertCount(9, $fencer->attributes());

        $reg = Registration::find(RegistrationData::REG2);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();
        $registrations = Registration::where('registration_event', EventData::MFCAT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();
        $output = $service->generate($se, $registrations);
        $xml = simplexml_load_string($output);
        $child = $xml->children()[0];
        $this->assertCount(2, $child->children());
    }

    public function testGenerateTeam()
    {
        $se = SideEvent::find(EventData::MFTEAM);
        $registrations = Registration::where('registration_event', EventData::MFTEAM)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();
        $service = new RegistrationXMLService();
        $output = $service->generate($se, $registrations);
        $xml = simplexml_load_string($output);
        $this->assertCount(2, $xml);
        $this->assertEquals("BaseCompetitionParEquipes", $xml->getName());
        $this->assertEquals(2, $xml->count());
        $this->assertEquals(2, count($xml));

        $child = $xml->children()[0];
        $this->assertEquals("Tireurs", $child->getName());
        $this->assertCount(4, $child->children());
        $fencer = $child->children()[0];
        $this->assertEquals("Tireur", $fencer->getName());
        $this->assertCount(10, $fencer->attributes()); // includes team name

        $child = $xml->children()[1];
        $this->assertEquals("Equipes", $child->getName());
        $this->assertCount(2, $child->children());
        $equipe = $child->children()[0];
        $this->assertEquals("Equipe", $equipe->getName());
        $this->assertCount(4, $equipe->attributes());
    }
}
