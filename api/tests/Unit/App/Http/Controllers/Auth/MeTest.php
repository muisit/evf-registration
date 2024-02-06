<?php

namespace Tests\Unit\App\Http\Controllers\Auth;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Unit\TestCase;
use Tests\Support\Data\AccreditationUser as CodeUserData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\Event as EventData;

class MeTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        RegistrarData::create();
    }

    /**
     * Testing the Me route
     *
     * @return void
     */
    public function testUnauthRoute()
    {
        $response = $this->session([])->call('GET', route('auth.me'));
        $this->assertNotEmpty($response);
        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertTrue(isset($output['token']));
        $this->assertFalse($output['status']);
        $this->assertNotEmpty($output['token']);
        $this->assertEmpty($output['credentials']);
        $this->assertEmpty($output['username']);
        $this->assertEmpty($output['countryId']);
    }

    public function testAuthRoute()
    {
        $response = $this->session(['wpuser' => UserData::TESTUSER])->get('/auth/me');

        $this->assertNotEmpty($response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertTrue(isset($output['token']));
        $this->assertTrue($output['status']);
        $this->assertNotEmpty($output['token']);
        $this->assertEquals(['user', 'sysop'], $output['credentials']);
        $this->assertEquals('Test User', $output['username']);
        $this->assertEmpty($output['countryId']);
    }

    public function testAuthByCodeRoute()
    {
        $response = $this->session(['accreditationuser' => CodeUserData::ADMIN])->get('/auth/me');

        $this->assertNotEmpty($response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertTrue(isset($output['token']));
        $this->assertTrue($output['status']);
        $this->assertNotEmpty($output['token']);
        $this->assertCount(5, $output['credentials']);
        $this->assertContains('code', $output['credentials']);
        $this->assertContains('organisation', $output['credentials']);
        $this->assertContains('organiser', $output['credentials']);
        $this->assertContains('organisation:1', $output['credentials']);
        $this->assertContains('organiser:1', $output['credentials']);
        $this->assertEquals('General Code', $output['username']);
        $this->assertEmpty($output['countryId']);
        $this->assertNotEmpty($output['eventId']);
        $this->assertEquals(EventData::EVENT1, $output['eventId']);
    }

    public function testHodRoute()
    {
        $response = $this->session(['wpuser' => UserData::TESTUSERHOD])->get('/auth/me');

        $this->assertNotEmpty($response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertTrue(isset($output['token']));
        $this->assertTrue($output['status']);
        $this->assertNotEmpty($output['token']);
        $this->assertEquals(['user', 'hod', 'hod:12'], $output['credentials']);
        $this->assertEquals('Test User6', $output['username']);
        $this->assertNotEmpty($output['countryId']);
    }
}
