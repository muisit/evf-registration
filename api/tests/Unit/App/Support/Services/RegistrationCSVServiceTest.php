<?php

namespace Tests\Unit\App\Support;

use App\Models\SideEvent;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Fencer;
use App\Support\Services\RegistrationCSVService;
use Tests\Support\Data\Event as MainEventData;
use Tests\Support\Data\SideEvent as EventData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Unit\TestCase;

class RegistrationCSVServiceTest extends TestCase
{
    public function testGenerate()
    {
        $se = SideEvent::find(EventData::MFCAT1);
        $reg = Registration::find(RegistrationData::REG2);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();
        $registrations = Registration::where('registration_event', EventData::MFCAT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();

        $service = new RegistrationCSVService();
        $headers = [
            "name", "firstname", "country", "country_abbr", "year-of-birth", "date", "event",
            "role", "organisation", "organisation_abbr", "type", "cat", "gender", "team", "picture"];

        $output = $service->generate($registrations, $headers);
        $this->assertCount(3, $output);
        $this->assertCount(15, $output[0]);
        $this->assertCount(15, $output[1]);
        $this->assertCount(15, $output[2]);
        $this->assertEquals("name", $output[0][0]);
        $this->assertEquals("De La Teste", $output[1][0]);
        $this->assertEquals("Tést", $output[1][1]);
        $this->assertEquals("Germany", $output[1][2]);
        $this->assertEquals("GER", $output[1][3]);
        $this->assertEquals("1983", $output[1][4]);
        //$this->assertEquals("", $output[1][5]); // competition date changes each test
        $this->assertEquals("Men's Foil Category 1", $output[1][6]);
        $this->assertEquals("Participant", $output[1][7]);
        $this->assertEquals("Germany", $output[1][8]);
        $this->assertEquals("GER", $output[1][9]);
        $this->assertEquals("Participant", $output[1][10]);
        $this->assertEquals("1", $output[1][11]);
        $this->assertEquals("Male", $output[1][12]);
        $this->assertEquals(null, $output[1][13]);
        $this->assertEquals("NONE", $output[1][14]);
        $this->assertEquals("Testita", $output[2][0]);
        $this->assertEquals("2", $output[2][11]);
        $this->assertEquals("NEW", $output[2][14]);
    }

    public function testIsCompetition()
    {
        $se = SideEvent::find(EventData::MFCAT1);
        $reg = Registration::find(RegistrationData::REG2);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();
        $registrations = Registration::where('registration_event', EventData::MFCAT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();

        $service = new RegistrationCSVService();
        $headers = [
            "name", "firstname", "country", "country_abbr", "year-of-birth", "date", "event",
            "role", "organisation", "organisation_abbr", "type", "cat", "gender", "team", "picture"];

        $output = $service->generate($registrations, $headers, (object)['isCompetition' => true]);
        $this->assertEquals("Athlete", $output[1][7]); // role
        $this->assertEquals("Athlete", $output[1][10]); // type
        $this->assertEquals("Athlete", $output[2][7]);
        $this->assertEquals("Athlete", $output[2][10]);
    }

    public function testGender()
    {
        $se = SideEvent::find(EventData::MFCAT1);
        $reg = Registration::find(RegistrationData::REG2);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();
        $registrations = Registration::where('registration_event', EventData::MFCAT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();

        $service = new RegistrationCSVService();
        $headers = [
            "name", "firstname", "country", "country_abbr", "year-of-birth", "date", "event",
            "role", "organisation", "organisation_abbr", "type", "cat", "gender", "team", "picture"];

        $output = $service->generate($registrations, $headers, (object)['gender' => 'F']);
        $this->assertEquals("Male (wrong gender)", $output[1][12]); // gender
    }

    public function testCategory()
    {
        $se = SideEvent::find(EventData::MFCAT1);
        $reg = Registration::find(RegistrationData::REG2);
        $reg->registration_event = EventData::MFCAT1;
        $reg->save();
        $registrations = Registration::where('registration_event', EventData::MFCAT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();

        $service = new RegistrationCSVService();
        $headers = [
            "name", "firstname", "country", "country_abbr", "year-of-birth", "date", "event",
            "role", "organisation", "organisation_abbr", "type", "cat", "gender", "team", "picture"];

        $output = $service->generate($registrations, $headers, (object)['category' => 3]);
        $this->assertEquals("1 (wrong category)", $output[1][11]); // category
    }

    public function testRoles()
    {
        $registrations = Registration::where('registration_mainevent', MainEventData::EVENT1)
            ->with(['fencer', 'fencer.country', 'country', 'sideEvent', 'role'])
            ->get()->all();

        $service = new RegistrationCSVService();
        $headers = [
            "name", "firstname", "country", "country_abbr", "year-of-birth", "date", "event",
            "role", "organisation", "organisation_abbr", "type", "cat", "gender", "team", "picture"];

        $output = $service->generate($registrations, $headers);
        $this->assertCount(19, $output);

        $this->assertNotEmpty($output[8][5]); // no side event
        $this->assertEquals("Cocktail Dinatoire", $output[8][6]); // no event title
        $this->assertEquals("Participant", $output[8][7]); // role

        $this->assertEquals("", $output[13][5]); // no side event
        $this->assertEquals("", $output[13][6]); // no event title
        $this->assertEquals("Head of Delegation", $output[13][7]); // role
        $this->assertEquals("Germany", $output[13][8]); // org
        $this->assertEquals("GER", $output[13][9]); // org abbr
        $this->assertEquals("Official", $output[13][10]);
        $this->assertEquals(4, $output[13][11]);

        $this->assertEquals("Coach", $output[14][7]); // role
        $this->assertEquals("Germany", $output[14][8]); // org
        $this->assertEquals("GER", $output[14][9]); // org abbr
        $this->assertEquals("Official", $output[14][10]);

        $this->assertEquals("Referee", $output[15][7]); // role
        $this->assertEquals("Organisation EVF Individual Championships", $output[15][8]); // org
        $this->assertEquals("Org", $output[15][9]); // org abbr
        $this->assertEquals("Official", $output[15][10]);

        $this->assertEquals("EVFC Director", $output[16][7]); // role
        $this->assertEquals("European Veterans Fencing", $output[16][8]); // org
        $this->assertEquals("EVF", $output[16][9]); // org abbr
        $this->assertEquals("Official", $output[16][10]);
    }
}
