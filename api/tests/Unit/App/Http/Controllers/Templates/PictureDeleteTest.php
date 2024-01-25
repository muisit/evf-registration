<?php

namespace Tests\Unit\App\Http\Controllers\Templates;

use App\Models\AccreditationTemplate;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;

class PictureDeleteTest extends TestCase
{
    public function testRoute()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(404);

        $output = $this->response->json();
        $this->assertTrue($output !== false);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(404);
    }

    public function testUnAuthorised()
    {
        $this->session(['_token' => 'aaa'])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERREGISTRAR])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERGENHOD])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER4])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

         // user id does not exist
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::NOSUCHID])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove?event=' . EventData::EVENT1, [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }

    public function testEventNotRequired()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove', [], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(404);
    }

    public function testCsrfGuarded()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/templates/' . TemplateData::ATHLETE . '/picture/a928a/remove', [], ['X-CSRF-Token' => 'bbb'])
            ->assertStatus(400);
    }
}
