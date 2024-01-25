<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use App\Models\Event;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class SaveRolesTest extends TestCase
{
    public function testData()
    {
        return ['roles' => [['id' => -1, 'userId' => UserData::TESTUSER, 'role' => 'organiser']]];
    }

    public function testRoute()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa']);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals('ok', $output['status']);
        $this->assertEmpty(isset($output['messages']));
    }

    public function testUnAuthorised()
    {
        $this->session(['_token' => 'aaa'])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER4])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // user id does not exist
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::NOSUCHID])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }

    public function testRequiresCsrf()
    {
        $this->session(['_token' => 'bbb', 'wpuser' => UserData::TESTUSER])
            ->post('/events/roles?event=' . EventData::EVENT1, $this->testData(), ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(400);
    }
}
