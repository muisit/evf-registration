<?php

namespace Tests\Unit\App\Http\Controllers\Templates;

use App\Models\AccreditationTemplate;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use App\Support\Services\PDFGenerator;
use Illuminate\Foundation\Application;

class FontsTest extends TestCase
{
    public function testRoute()
    {
        $response = $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/templates/fonts')
            ->assertStatus(200);

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertCount(42, $output);

        $this->session(['wpuser' => UserData::TESTUSERORGANISER])
            ->get('/templates/fonts?event=' . EventData::EVENT1)
            ->assertStatus(200);
    }

    public function testUnAuthorised()
    {
        $this->get('/templates/fonts')
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/templates/fonts')
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERREGISTRAR])
            ->get('/templates/fonts')
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/templates/fonts')
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERGENHOD])
            ->get('/templates/fonts')
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/templates/fonts')
            ->assertStatus(403);

         // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/templates/fonts')
            ->assertStatus(403);

        // organiser requires event
        $this->session(['wpuser' => UserData::TESTUSERORGANISER])
            ->get('/templates/fonts')
            ->assertStatus(403);
    }
}
