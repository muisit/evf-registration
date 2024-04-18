<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use App\Models\Event;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class SaveSidesTest extends TestCase
{
    public function testData()
    {
        return ['sides' => [['id' => -1, 'title' => 'A title', 'starts' => '2020-01-01']]];
    }

    public function testRoute()
    {
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa']);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals('ok', $output['status']);
        $this->assertEmpty(isset($output['messages']));
    }

    public function testUnAuthorised()
    {
        $this->session(['_token' => 'aaa'])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER4])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // user id does not exist
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::NOSUCHID])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }

    public function testRequiresCsrf()
    {
        $this->session(['_token' => 'bbb', 'wpuser' => UserData::TESTUSER])
            ->post('/events/sides?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(419);
    }
}
