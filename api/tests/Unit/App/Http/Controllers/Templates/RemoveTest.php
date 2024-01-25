<?php

namespace Tests\Unit\App\Http\Controllers\Templates;

use App\Models\Event;
use App\Models\AccreditationTemplate;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Event as EventData;

class RemoveTest extends TestCase
{
    public function testRoute()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa']);

        // we expect an Ok result nd a 200 status
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals('ok', $output['status']);
        $this->assertEmpty(isset($output['messages']));
        $this->assertStatus(200);

        // template should be gone
        $this->assertEmpty(AccreditationTemplate::find(TemplateData::ATHLETE));
    }

    public function testUnAuthorised()
    {
        $this->session(['_token' => 'aaa'])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'bbb'])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(400);

        // test user 5 has no privileges
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER4])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::ATHLETE]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(403);
    }

    public function testValidateId()
    {
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->post('/templates/remove', ['template' => ['id' => TemplateData::NOSUCHID]], ['X-CSRF-Token' => 'aaa'])
            ->assertStatus(404);

        $this->assertNotEmpty(AccreditationTemplate::find(TemplateData::ATHLETE));
    }
}
