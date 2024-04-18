<?php

namespace Tests\Unit\App\Http\Controllers\Registrations;

use App\Models\Country;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\SideEvent as SideEventData;
use Carbon\Carbon;

class SaveTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        RegistrationData::create();
        RegistrarData::create();
        EventRoleData::create();
    }

    private function createRegistrationData()
    {
        return [
            "id" => RegistrationData::REG1,
            "country" => Country::GER,
            "fencerId" => FencerData::MCAT1,
            "payment" => 'G',
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT1,
            'team' => null
        ];
    }

    public function testRoute()
    {
        $data = $this->createRegistrationData();
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa']);

        // we expect the updated registration and a 200 status
        $output = $response->json();
        $this->assertEquals(RegistrationData::REG1, $output['id']);
        $this->assertEquals('G', $output['payment']);
        $this->assertStatus(200);

        $data["id"] = 0;
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa']);
        // we expect a non-empty JSON result and a 200 status
        $output = $response->json();
        $this->assertNotEmpty($output);
        $this->assertEquals($data['fencerId'], $output['fencerId']);
        $this->assertEquals($data['payment'], $output['payment']);
        $this->assertEquals(0, $output['roleId']); // null=>0 conversion
        $this->assertEquals($data['sideEventId'], $output['sideEventId']);
        $this->assertEquals($data['team'], $output['team']);
        $this->assertStatus(200);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
      
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        // Registration and HOD are both linked to Country::GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $data = $this->createRegistrationData();
        $this->session(['_token' => 'aaa'])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'aaa'])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'bbb'])
            ->assertStatus(419);

        // test user 5 has no privileges to view the registration
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // if country is set, it must match that of the registration
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations?country='  . Country::ITA, ['event' => EventData::EVENT1, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::ITA, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $data['countryId'] = Country::ITA;
        // set to GER by default
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to GER explicitely, but overridden because HoD is GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations?country='  . Country::GER, ['event' => EventData::EVENT1, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to match registration, but overriden because HoD is GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations', ['event' => EventData::EVENT1, 'country' => Country::ITA, 'registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }

    public function testValidateFencer()
    {
        $data = $this->createRegistrationData();
        $data['fencerId'] = FencerData::NOSUCHFENCER;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['fencerId'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['fencerId'] = '1234567890123456789012345678901234567890123456';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }

    public function testValidateSideEvent()
    {
        $data = $this->createRegistrationData();
        $data['sideEventId'] = SideEventData::NOSUCHEVENT;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['sideEventId'] = null; // not both sideEvent and role can be null
        $fencer['roleId'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['sideEventId'] = '1234567890123456789012345678901234567890123456';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }

    public function testValidateRole()
    {
        $data = $this->createRegistrationData();
        $data['roleId'] = 9829911;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['roleId'] = null; // not both sideEvent and role can be null
        $fencer['sideEventId'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['roleId'] = '1234567890123456789012345678901234567890123456';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }

    public function testValidateCountry()
    {
        $data = $this->createRegistrationData();
        $data['countryId'] = 0;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['countryId'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }

    public function testValidatePayment()
    {
        $data = $this->createRegistrationData();
        $data['payment'] = 'X';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $data['payment'] = 0;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $data['payment'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations', ['registration' => $data], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }
}
