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

class ExampleTest extends TestCase
{
    public function testRoute()
    {
        $sut = $this;
        $this->app->bind(PDFGenerator::class, function (Application $app) use ($sut) {
            $generator = $sut->createMock(PDFGenerator::class);
            $generator->expects($sut->once())->method('generate');
            $generator->expects($sut->once())->method('save');
            return $generator;
        });
        // returns 200, because tempname() creates an empty file which is returned, even
        // though the generator does not really save content to it
        $this->session(['wpuser' => UserData::TESTUSER])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(200);
    }

    public function testRouteOrganiser()
    {
        $sut = $this;
        $this->app->bind(PDFGenerator::class, function (Application $app) use ($sut) {
            $generator = $sut->createMock(PDFGenerator::class);
            $generator->expects($sut->once())->method('generate');
            $generator->expects($sut->once())->method('save');
            return $generator;
        });

        $this->session(['wpuser' => UserData::TESTUSERORGANISER])
            ->get('/templates/' . TemplateData::ATHLETE . '/print?event=' . EventData::EVENT1)
            ->assertStatus(200);
    }

    public function testRouteOrganiserRequiresEvent()
    {
        $sut = $this;
        $this->app->bind(PDFGenerator::class, function (Application $app) use ($sut) {
            $generator = $sut->createMock(PDFGenerator::class);
            $generator->expects($sut->once())->method('generate');
            $generator->expects($sut->once())->method('save');
            return $generator;
        });

        $this->session(['wpuser' => UserData::TESTUSERORGANISER])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);
    }

    public function testUnAuthorised()
    {
        $this->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(401);

        // test user 5 has no privileges
        $this->session(['wpuser' => UserData::TESTUSER5])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERREGISTRAR])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERHOD])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);

        $this->session(['wpuser' => UserData::TESTUSERGENHOD])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);

        // cashier, so organisation but not organiser
        $this->session(['wpuser' => UserData::TESTUSER4])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);

         // user id does not exist
        $this->session(['wpuser' => UserData::NOSUCHID])
            ->get('/templates/' . TemplateData::ATHLETE . '/print')
            ->assertStatus(403);
    }
}
