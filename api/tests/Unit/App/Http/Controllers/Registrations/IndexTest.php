<?php

namespace Tests\Unit\App\Http\Controllers\Registrations;

use App\Models\Country;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Unit\TestCase;

class IndexTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        RegistrationData::create();
        RegistrarData::create();
    }

    public function testRoute()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/registrations?event=' . EventData::EVENT1);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(6, $output['registrations']); // 6 organisation roles
        $this->assertCount(3, $output['fencers']); // MCAT4, MCAT5 and MCAT3 (invited to Gala)
    }

    public function testRouteWithCountry()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/registrations?event=' . EventData::EVENT1 . '&country=' . Country::ITA);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(2, $output['registrations']); // 2 registrations for ITA
        $this->assertCount(1, $output['fencers']); // only MCAT2
    }

    public function testRouteWithHod()
    {
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/registrations?event=' . EventData::EVENT1);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(8, $output['registrations']); // 8 registrations for GER
        $this->assertCount(5, $output['fencers']); // MCAT1, MCAT1B, MCAT1C, MCAT5, WCAT1

        // country parameter has no effect
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/registrations?event=' . EventData::EVENT1 . '&country=' . Country::ITA);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(8, $output['registrations']);
        $this->assertCount(5, $output['fencers']);
    }

    public function testUnAuthorised()
    {
        $this->get('/registrations?event=' . EventData::EVENT1)
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/registrations?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/registrations?event=' . EventData::EVENT1)
            ->assertStatus(403);
    }

    public function testNotExisting()
    {
        $this->get('/registrations?event=' . EventData::NOSUCHEVENT)
            ->assertStatus(401);
    }
}
