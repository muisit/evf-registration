<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class OverviewTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        RegistrationData::create();
    }

    public function testRoute()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/events/' . EventData::EVENT1 . '/overview');

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(7, $output);

        // test user 2 is sysop
        $this->session(['wpuser' => UserData::TESTUSER2])
            ->get('/events/' . EventData::EVENT1 . '/overview')
            ->assertStatus(200);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/events/' . EventData::EVENT1 . '/overview')
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/events/' . EventData::EVENT1 . '/overview')
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/events/' . EventData::EVENT1 . '/overview')
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/events/' . EventData::EVENT1 . '/overview')
            ->assertStatus(403);
    }

    public function testNotExisting()
    {
        $this->get('/events/' . EventData::NOSUCHEVENT . '/overview')
            ->assertStatus(401);
    }
}
