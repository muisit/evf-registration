<?php

namespace Tests\Unit\App\Support;

use Tests\Unit\TestCase;
use Tests\Support\Data\WPUser as UserData;
use Illuminate\Support\Facades\RateLimiter;

class LoginTest extends TestCase
{
    public function fixtures()
    {
        RateLimiter::clear(sha1('POST|localhost|auth/login|127.0.0.1'));
        UserData::create();
    }

    public function testRoute()
    {
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'password123'],
                ['X-CSRF-Token' => 'aaa']
            );

        $this->assertNotEmpty($this->response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("ok", $output['status']);
        $this->assertEmpty($output['message']);
    }

    public function testLoginWithName()
    {
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'password123'],
                ['X-CSRF-Token' => 'aaa']
            );

        $this->assertNotEmpty($this->response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("ok", $output['status']);
        $this->assertEmpty($output['message']);
    }

    public function testRouteUnauth()
    {
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            );

        $this->assertNotEmpty($this->response);
        $output = $this->response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("error", $output['status']);
        $this->assertNotEmpty($output['message']);
    }

    public function testRateLimited()
    {
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            );
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            );
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            );
        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            );

        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            )
            ->assertStatus(429)
            ->assertHeader('X-Ratelimit-Limit', 2)
            ->assertHeader('X-Ratelimit-Remaining', 0);

        $this
            ->session(['_token' => 'aaa'])
            ->post(
                route('auth.login'),
                ['username' => 'test@example.com', 'password' => 'nosuchpass'],
                ['X-CSRF-Token' => 'aaa']
            )
            ->assertStatus(429)
            ->assertHeader('X-Ratelimit-Limit', 2)
            ->assertHeader('X-Ratelimit-Remaining', 0);
    }
}
