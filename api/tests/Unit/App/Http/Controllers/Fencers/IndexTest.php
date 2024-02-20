<?php

namespace Tests\Unit\App\Http\Controllers\Fencers;

use App\Models\Country;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\EventRole as EventRoleData;

class IndexTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        FencerData::create();
        RegistrarData::create();
        EventRoleData::create();
    }

    public function testRoute()
    {
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->get('/fencers?country=' . Country::ITA);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(2, $output);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers?country=' . Country::GER)
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/fencers?country=' . Country::GER)
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/fencers?country=' . Country::GER)
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/fencers?country=' . Country::GER)
            ->assertStatus(403);
    }

    public function testCountryOverride()
    {
        // requires a country value
        // country value is set to the HoD default
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers')
            ->assertStatus(200);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers?country=' . Country::GER)
            ->assertStatus(200);

        // wrong country
        // even though a country is provided, country value
        // is set to the country of the HoD
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers?country=' . Country::ITA)
            ->assertStatus(200);
    }

    public function testCountryRequired()
    {
        // requires a country value
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/fencers')
            ->assertStatus(403);
    }
}
