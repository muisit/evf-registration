<?php

namespace Tests\Unit\App\Http\Controllers\Accreditations;

use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use App\Jobs\RegenerateBadges;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Queue;

class RegenerateTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        AccreditationData::create();
    }

    public function testRoute()
    {
        Queue::fake();
        $response = $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("ok", $output['status']);
        $this->assertEmpty($output['message']);

        Queue::assertPushed(RegenerateBadges::class, 1);
    }

    public function testAuthorized()
    {
        // testuser is WP administrator, so sysop
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(200);

        // test user 2 is editor with manage_rankings cap, so sysop
        $this->session(['wpuser' => UserData::TESTUSER2])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(200);

        // test user 4 is organisation
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // registrar not allowed
        $this->session(['wpuser' => UserData::TESTUSERREGISTRAR])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // hod not allowed
        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // superhod not allowed
        $this->session(['wpuser' => UserData::TESTUSERGENHOD])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // cashier not allowed
        $this->session(['wpuser' => UserData::TESTUSER3])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // accreditation not allowed
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/accreditations/regenerate?event=' . EventData::EVENT1)
            ->assertStatus(403);
    }

    public function testNotExisting()
    {
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/accreditations/regenerate?event=' . EventData::NOSUCHEVENT)
            ->assertStatus(404);
    }
}
