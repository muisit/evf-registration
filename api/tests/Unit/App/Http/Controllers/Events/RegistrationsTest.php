<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use App\Models\Country;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Unit\TestCase;

class RegistrationsTest extends TestCase
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
            ->get('/events/' . EventData::EVENT1 . '/registrations');

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(3, $output['registrations']);
        $this->assertCount(2, $output['fencers']);
    }

    public function testRouteWithCountry()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/events/' . EventData::EVENT1 . '/registrations?country=' . Country::ITA);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(4, $output['registrations']);
        $this->assertCount(4, $output['fencers']);
    }

    public function testRouteWithHod()
    {
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/events/' . EventData::EVENT1 . '/registrations');

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(3, $output['registrations']);
        $this->assertCount(3, $output['fencers']);

        // country parameter has no effect
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/events/' . EventData::EVENT1 . '/registrations?country=' . Country::ITA);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertCount(3, $output['registrations']);
        $this->assertCount(3, $output['fencers']);
    }

    public function testUnAuthorised()
    {
        $this->get('/events/' . EventData::EVENT1 . '/registrations')
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/events/' . EventData::EVENT1 . '/registrations')
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/events/' . EventData::EVENT1 . '/registrations')
            ->assertStatus(403);
    }

    public function testNotExisting()
    {
        $this->get('/events/' . EventData::NOSUCHEVENT . '/registrations')
            ->assertStatus(401);
    }
}
