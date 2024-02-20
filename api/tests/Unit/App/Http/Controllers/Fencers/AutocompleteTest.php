<?php

namespace Tests\Unit\App\Http\Controllers\Fencers;

use App\Models\Country;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Event as EventData;

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
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->get('/fencers/autocomplete?name=T&event=' . EventData::EVENT1);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(2, $output);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers/autocomplete?name=T&event=' . EventData::EVENT1)
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/fencers/autocomplete?event=' . EventData::EVENT1)
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/fencers/autocomplete?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/fencers/autocomplete?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // GET route only
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->post('/fencers/autocomplete?event=' . EventData::EVENT1, ["a" => 2])
            ->assertStatus(405);

        // test user 4 is not organisation for NOSUCHEVENT
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/fencers/autocomplete?name=T&event=' . EventData::NOSUCHEVENT)
            ->assertStatus(403);
    }

    public function testRequiresCountry()
    {
        // requires a country value
        // country value is set to the HoD default
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers/autocomplete?name=T&event=' . EventData::EVENT1)
            ->assertStatus(200);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers/autocomplete?name=T&country=' . Country::GER . '&event=' . EventData::EVENT1)
            ->assertStatus(200);

        // wrong country
        // even though a country is provided, country value
        // is set to the country of the HoD
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/fencers/autocomplete?name=T&country=' . Country::ITA . '&event=' . EventData::EVENT1)
            ->assertStatus(200);
    }
}
