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

class PhotoStateTest extends TestCase
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
        return [
            "id" => FencerData::MCAT1,
            "photoStatus" => 'Y'
        ];
    }

    public function testRoute()
    {
        $fencer = $this->createFencerData();
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa']);

        // we expect a ResponseStatus
        $output = $response->json();
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("ok", $output['status']);
        $this->assertEmpty($output['message']);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $fencer = $this->createFencerData();
        $this->session(['_token' => 'aaa'])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'aaa'])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'bbb'])
            ->assertStatus(419);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
      
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // Fencer and HOD are both linked to Country::GER
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

            // test user 5 has no privileges to view the fencer country
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // if country is set, it must match that of the fencer
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['country' => Country::ITA, 'fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $fencer['countryId'] = Country::ITA;
        // set to GER by default
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to GER explicitely, but overridden
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/' . $fencer['id'] . '/photostate?country=' . Country::GER, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // set to match fencer, but overriden
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/fencers/' . $fencer['id'] . '/photostate?country=' . Country::ITA, ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $fencer["id"] = 0;
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa']);
        // we expect a non-empty JSON result and a 406 status
        $output = $response->json();
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("error", $output['status']);
        $this->assertNotEmpty($output['message']);
        $this->assertStatus(403);
    }

    public function testValidateState()
    {
        $fencer = $this->createFencerData();
        $fencer['photoStatus'] = 0;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        // photoState is a required value
        $fencer['photoStatus'] = null;
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);

        $fencer['photoStatus'] = 'G';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/fencers/' . $fencer['id'] . '/photostate', ['fencer' => $fencer], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(422);
    }
}
