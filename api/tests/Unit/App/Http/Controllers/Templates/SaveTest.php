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

class SaveTest extends TestCase
{
    private function createTemplateData()
    {
        $template = AccreditationTemplate::where('id', TemplateData::COUNTRY)->first();
        return [
            "id" => $template->id,
            "name" => $template->name,
            "content" => $template->content,
            "eventId" => $template->event_id,
            "isDefault" => $template->is_default
        ];
    }

    public function testRoute()
    {
        $template = $this->createTemplateData();
        $template['name'] = 'Pete';
        $response = $this->withSession(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template]);

        // we expect the updated fencer and a 200 status
        $output = $response->json();
        $this->assertEquals(TemplateData::COUNTRY, $output['id']);
        $this->assertEquals('Pete', $output['name']);
        $this->assertStatus(200);

        $fencer["id"] = 0;
        $response = $this->withSession(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template]);

            // we expect a non-empty JSON result and a 200 status
        $output = $response->json();
        $this->assertNotEmpty($output);
        $this->assertEquals($template['name'], $output['name']);
        $this->assertEquals($template['content'], json_encode($output['content']));
        $this->assertEquals($template['eventId'], $output['eventId']);
        $this->assertEquals($template['isDefault'], $output['isDefault']);
        $this->assertStatus(200);

        $template = $this->createTemplateData();
        $template['name'] = 'Pete';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERORGANISER])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $template = $this->createTemplateData();
        $this->session(['_token' => 'aaa'])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(401);

        // CSRF check
        $this->session(['_token' => 'bbb'])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(419);

        // test user 5 has no privileges
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER5])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSERHOD])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(403);

        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER4])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(403);
    }

    public function testValidateName()
    {
        $template = $this->createTemplateData();
        $template['name'] = '';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(422);
    }

    public function testValidateDefault()
    {
        $template = $this->createTemplateData();
        $template['isDefault'] = 'R';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(422);
    }

    public function testValidateContent()
    {
        $template = $this->createTemplateData();
        $template['content'] = 'aaa';
        $this->session(['_token' => 'aaa', 'wpuser' => UserData::TESTUSER])
            ->withHeaders(['X-CSRF-Token' => 'aaa'])
            ->post('/templates', ['template' => $template])
            ->assertStatus(422);
    }
}
