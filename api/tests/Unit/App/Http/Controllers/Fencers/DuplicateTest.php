<?php

namespace Tests\Unit\App\Http\Controllers\Fencers;

use App\Models\Country;
use App\Models\Fencer;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\EventRole as EventRoleData;

class DuplicateTest extends TestCase
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
            // irrelevant, but possibly transmitted data
            "gender" => $fencer->fencer_gender,
            // additional garbage that may or may not be transmitted
            "birthYear" => 1974
        ];
    }

    public function testRoute()
    {
        $fencer = $this->createFencerData();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa']);

        // we expect an empty result and a 200 status
        $output = $this->response->json();
        $this->assertEmpty($output);
        $this->assertStatus(200);

        $fencer["id"] = FencerData::NOSUCHFENCER;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa']);
        // we expect a non-empty JSON result and a 406 status
        $output = $this->response->json();
        $this->assertNotEmpty($output);
        $this->assertEquals(FencerData::MCAT1, $output['id']);
        $this->assertStatus(409);

        $fencer['id'] = FencerData::MCAT1;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
      
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        // MCAT1 and HOD are both linked to Country::GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $fencer = $this->createFencerData();
        $this->session(['_token' => 'aaa'])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'aaa'])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'bbb'])
            ->assertStatus(400);

        // test user 5 has no privileges to view the fencer country
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // if country is set, it must match that of the fencer
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/duplicate?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/duplicate', ['country' => Country::ITA, 'fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $fencer['countryId'] = Country::ITA;
        // set to GER by default
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/duplicate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to GER explicitely, but overridden
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/duplicate?country=' . Country::GER, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to match fencer, but overriden
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/duplicate?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // not a GET route
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->get('/fencers/duplicate?fencer=' . json_encode($fencer))
            ->assertStatus(405);
    }

    public function testRequiresCountry()
    {
        $fencer = $this->createFencerData();
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/duplicate?country=' . Country::GER, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);

        // wrong country
        // even though a country is provided, country value
        // is set to the country of the HoD
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/duplicate?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }
}
