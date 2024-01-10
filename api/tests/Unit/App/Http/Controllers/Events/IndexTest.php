<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use App\Models\Event;
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
            ->get('/events')
            ->assertStatus(200);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/events')
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

    public function testEffectOfConfig()
    {
        $event = Event::find(EventData::EVENT1);
        $event->event_config = json_encode(['use_registration' => true]);
        $event->save();
        $this->session(['wpuser' => UserData::TESTUSER])->get('/events');
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(1, $output);

        $event->event_config = json_encode(['use_registration' => false]);
        $event->save();
        $this->session(['wpuser' => UserData::TESTUSER])->get('/events');
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(0, $output);

        $event->event_config = json_encode(['use_registrations' => true]);
        $event->save();
        $this->session(['wpuser' => UserData::TESTUSER])->get('/events');
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(0, $output);

        $event->event_config = '';
        $event->save();
        $this->session(['wpuser' => UserData::TESTUSER])->get('/events');
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(0, $output);
    }
}
