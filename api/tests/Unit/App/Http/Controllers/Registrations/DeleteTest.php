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

class DeleteTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        RegistrationData::create();
        RegistrarData::create();
        EventRoleData::create();
    }

    private function saveRegistration()
    {
        $reg = new Registration();
        $reg->registration_mainevent = EventData::EVENT1;
        $reg->registration_country = Country::GER;
        $reg->registration_fencer = FencerData::MCAT1;
        $reg->registration_event = SideEventData::MFCAT1;
        $reg->registration_role = null;
        $reg->registration_payment = 'G';
        $reg->registration_team = null;
        $reg->save();
        return $reg;
    }

    public function testRoute()
    {
        $reg = $this->saveRegistration();
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa']);

        // we a 200 status and a ResponseStatus of 'ok'
        $output = $response->json();
        $this->assertEquals('ok', $output['status']);
        $this->assertStatus(200);
        $this->assertEmpty(Registration::find($reg->registration_id));

        $reg = $this->saveRegistration();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        $reg = $this->saveRegistration();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
      
        $reg = $this->saveRegistration();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        // Registration and HOD are both linked to Country::GER
        $reg = $this->saveRegistration();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $reg = $this->saveRegistration();
        $this->session(['_token' => 'aaa'])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'aaa'])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'bbb'])
            ->assertStatus(419);

        // test user 5 has no privileges to delete the registration
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // if country is set, it must match that of the registration
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::ITA, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/registrations/delete?country=' . Country::ITA, ['event' => EventData::EVENT1, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $reg->registration_country = Country::ITA;
        $reg->save();
        // set to GER by default
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::GER, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to GER explicitely, but overridden because HoD is GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations/delete?country=' . Country::GER, ['event' => EventData::EVENT1, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to match registration, but overriden because HoD is GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/registrations/delete', ['event' => EventData::EVENT1, 'country' => Country::ITA, 'registration' => ['id' => $reg->registration_id]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }
}
