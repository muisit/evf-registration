<?php

namespace Tests\Unit\App\Support;

use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Illuminate\Support\Facades\RateLimiter;

class LogoutTest extends TestCase
{
    public function testRoute()
    {
        UserData::create();

        $this
            ->session(['wpuser' => UserData::TESTUSER])
            ->get(route('auth.logout'));

        $this->assertNotEmpty($this->response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("ok", $output['status']);
        $this->assertEmpty($output['message']);
        $this->assertEmpty($this->app['session']->get('wpuser'));
    }
}
