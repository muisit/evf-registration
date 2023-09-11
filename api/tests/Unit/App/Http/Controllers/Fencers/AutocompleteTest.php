<?php

namespace Tests\Unit\App\Http\Controllers\Auth;

use App\Models\Country;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\EventRole as EventRoleData;

class AutocompleteTest extends TestCase
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
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->get('/fencers/autocomplete?name=T');

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(2, $output);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers/autocomplete?name=T')
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/fencers/autocomplete')
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/fencers/autocomplete')
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/fencers/autocomplete')
            ->assertStatus(403);

        // GET route only
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->post('/fencers/autocomplete', ["a" => 2])
            ->assertStatus(400);
    }

    public function testRequiresCountry()
    {
        // requires a country value
        // country value is set to the HoD default
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers/autocomplete?name=T')
            ->assertStatus(200);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers/autocomplete?name=T&country=' . Country::GER)
            ->assertStatus(200);

        // wrong country
        // even though a country is provided, country value
        // is set to the country of the HoD
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers/autocomplete?name=T&country=' . Country::ITA)
            ->assertStatus(200);
    }
}
