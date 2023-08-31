<?php

namespace Tests\Unit\App\Http\Controllers\Auth;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;

class MeTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
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
    }

    public function testAuthRoute()
    {
        $response = $this->session(['wpuser' => UserData::TESTUSER])->call('GET', route('auth.me'));

        $this->assertNotEmpty($response);
        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertTrue(isset($output['token']));
        $this->assertTrue($output['status']);
        $this->assertNotEmpty($output['token']);
        $this->assertEquals(['user', 'sysop'], $output['credentials']);
        $this->assertEquals('Test User', $output['username']);
    }
}
