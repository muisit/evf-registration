<?php

namespace Tests\Unit\App\Http\Controllers\Accreditations;

use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class OverviewTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        AccreditationData::create();
    }

    public function testRoute()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/accreditations/overview?event=' . EventData::EVENT1);

        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(13, $output);

        // test user 2 is sysop
        $this->session(['wpuser' => UserData::TESTUSER2])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(200);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/accreditations/overview?event=' . EventData::EVENT1)

            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // registrar not allowed
        $this->session(['wpuser' => UserData::TESTUSERREGISTRAR])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // hod not allowed
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // superhod not allowed
        $this->session(['wpuser' => UserData::TESTUSERGENHOD])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // cashier not allowed
        $this->session(['wpuser' => UserData::TESTUSER3])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // accreditation not allowed
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/accreditations/overview?event=' . EventData::EVENT1)
            ->assertStatus(403);
    }

    public function testNotExisting()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/accreditations/overview?event=' . EventData::NOSUCHEVENT)
            ->assertStatus(404);
    }
}
