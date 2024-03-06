<?php

namespace Tests\Unit\App\Http\Controllers\Templates;

use App\Models\Country;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;

class IndexTest extends TestCase
{
    public function testRoute()
    {
        $response = $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(200);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(5, $output); // 4 templates and a default

        $this->session(['wpuser' => UserData::TESTUSERORGANISER])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERREGISTRAR])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERGENHOD])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(403);

            // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/templates?event=' . EventData::EVENT1)
            ->assertStatus(403);
    }

    public function testEventRequired()
    {
        // requires an event value
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/templates')
            ->assertStatus(404);
    }
}
