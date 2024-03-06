<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use App\Models\Event;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class GetTest extends TestCase
{
    public function testRoute()
    {
        $response = $this->get('/events/' . EventData::EVENT1);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertNotEmpty($output['id']);
        $this->assertNotEmpty($output['name']);
        $this->assertNotEmpty($output['opens']);
        $this->assertNotEmpty($output['competitions']);
        $this->assertNotEmpty($output['sideEvents']);
        $this->assertNotEmpty($output['config']);
        $this->assertCount(6, $output); // these are all the fields

        // test user 2 is sysop
        $this->session(['wpuser' => UserData::TESTUSER2])
            ->get('/events')
            ->assertStatus(200);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/events')
            ->assertStatus(200);
    }

    public function testNoSuchEvent()
    {
        // user id does not exist
        $this->get('/events/' . EventData::NOSUCHEVENT)
            ->assertStatus(404);
    }

    public function testEffectOfConfig()
    {
        $event = Event::find(EventData::EVENT1);
        $event->event_config = json_encode(['use_accreditation' => true]);
        $event->save();
        $this->get('/events/' . EventData::EVENT1)->assertStatus(200);

        $event->event_config = json_encode(['use_accreditation' => false]);
        $event->save();
        $this->get('/events/' . EventData::EVENT1)->assertStatus(404);
    }

    public function testEffectOfOpen()
    {
        $event = Event::find(EventData::EVENT1);
        $event->event_open = '3000-01-01';
        $event->save();
        $this->get('/events/' . EventData::EVENT1)->assertStatus(200);

        $event->event_open = '2000-01-01';
        $event->save();
        $this->get('/events/' . EventData::EVENT1)->assertStatus(404);
    }

}
