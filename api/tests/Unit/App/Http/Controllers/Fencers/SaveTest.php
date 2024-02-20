<?php

namespace Tests\Unit\App\Http\Controllers\Fencers;

use App\Models\Country;
use App\Models\Fencer;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\EventRole as EventRoleData;
use Carbon\Carbon;

class SaveTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        FencerData::create();
        RegistrarData::create();
        EventRoleData::create();
    }

    private function createFencerData()
    {
        $fencer = Fencer::where('fencer_id', FencerData::MCAT1)->first();
        return [
            "id" => $fencer->fencer_id,
            "firstName" => $fencer->fencer_firstname,
            "lastName" => $fencer->fencer_surname,
            "dateOfBirth" => $fencer->fencer_dob,
            "countryId" => $fencer->fencer_country,
            "gender" => $fencer->fencer_gender,
        ];
    }

    public function testRoute()
    {
        $fencer = $this->createFencerData();
        $fencer['firstName'] = 'Pete';
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa']);

        // we expect the updated fencer and a 200 status
        $output = $response->json();
        $this->assertEquals(FencerData::MCAT1, $output['id']);
        $this->assertEquals('Pete', $output['firstName']);
        $this->assertStatus(200);

        $fencer["id"] = 0;
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa']);
        // we expect a non-empty JSON result and a 406 status
        $output = $response->json();
        $this->assertNotEmpty($output);
        $this->assertEquals($fencer['firstName'], $output['firstName']);
        $this->assertEquals($fencer['lastName'], $output['lastName']);
        $this->assertEquals($fencer['gender'], $output['gender']);
        $this->assertEquals($fencer['countryId'], $output['countryId']);
        $this->assertEquals($fencer['dateOfBirth'], $output['dateOfBirth']);
        $this->assertStatus(200);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
      
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        // Fencer and HOD are both linked to Country::GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $fencer = $this->createFencerData();
        $this->session(['_token' => 'aaa'])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'aaa'])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'bbb'])
            ->assertStatus(400);

        // test user 5 has no privileges to view the fencer country
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // if country is set, it must match that of the fencer
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['country' => Country::ITA, 'fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $fencer['countryId'] = Country::ITA;
        // set to GER by default
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to GER explicitely, but overridden
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers?country=' . Country::GER, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to match fencer, but overriden
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }

    public function testValidateFirstName()
    {
        $fencer = $this->createFencerData();
        $fencer['firstName'] = 'a';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['firstName'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['firstName'] = '1234567890123456789012345678901234567890123456';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['firstName'] = '123456789012345678901234567890123456789012345';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testValidateLastName()
    {
        $fencer = $this->createFencerData();
        $fencer['lastName'] = 'a';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['lastName'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['lastName'] = '1234567890123456789012345678901234567890123456';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['lastName'] = '123456789012345678901234567890123456789012345';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testValidateGender()
    {
        $fencer = $this->createFencerData();
        $fencer['gender'] = 'a';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['gender'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['gender'] = 'W';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['gender'] = 'F';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testValidateCountry()
    {
        $fencer = $this->createFencerData();
        $fencer['countryId'] = 0;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['countryId'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }

    public function testValidateDOB()
    {
        $fencer = $this->createFencerData();
        $fencer['dateOfBirth'] = 0;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        // date of birth is not a required value
        $fencer['dateOfBirth'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        $fencer['dateOfBirth'] = Carbon::now()->addMinutes(2)->toDateString();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }
}
