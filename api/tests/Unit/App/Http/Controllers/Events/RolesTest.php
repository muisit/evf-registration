<?php

namespace Tests\Unit\App\Http\Controllers\Events;

use App\Models\Event;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class RolesTest extends TestCase
{
    public function testRoute()
    {
        $response = $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/events/roles?event=' . EventData::EVENT1);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['roles']));
        $this->assertCount(5, $output['roles']);
        $this->assertTrue(isset($output['users']));
        $this->assertCount(10, $output['users']);
    }

    public function testUnAuthorised()
    {
        $this->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERREGISTRAR])
            ->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERGENHOD])
            ->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/events/roles?event=' . EventData::EVENT1)
            ->assertStatus(403);
    }
}
