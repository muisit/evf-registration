<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class IndexTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        EventData::create();
    }

    public function testRoute()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/events');

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(1, $output);

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
        $this->get('/events')
            ->assertStatus(401);
    }

    public function testSimpleUserNoResults()
    {
        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/events')
            ->assertStatus(200);
        $output = $this->response->getContent();
        $this->assertEquals("[]", $output);
    }

    public function testUserIdIncorrect()
    {
        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/events')
            ->assertStatus(401);
    }
}
