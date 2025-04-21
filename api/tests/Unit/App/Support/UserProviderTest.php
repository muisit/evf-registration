<?php

namespace Tests\Unit\App\Support;

use App\Models\WPUser;
use App\Support\UserProvider;
use Tests\Support\Data\WPUser as Data;
use Tests\Unit\TestCase;

class UserProviderTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
    }

    public function testRetrieveById()
    {
        $provider = new UserProvider();
        $user = $provider->retrieveById(Data::TESTUSER);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER, $user->getKey());

        $user = $provider->retrieveById(Data::TESTUSER2);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER2, $user->getKey());

        $user = $provider->retrieveById(-1);
        $this->assertEmpty($user);
    }

    public function testRetrieveWPUserById()
    {
        $provider = new UserProvider();
        $user = $provider->retrieveWPUserById(Data::TESTUSER);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER, $user->getKey());

        $user = $provider->retrieveWPUserById(Data::TESTUSER2);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER2, $user->getKey());

        $user = $provider->retrieveWPUserById(-1);
        $this->assertEmpty($user);
    }

    public function testRetrieveByToken()
    {
        $provider = new UserProvider();
        $user = $provider->retrieveByToken(Data::TESTUSER, "token");
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER, $user->getKey());

        $user = $provider->retrieveByToken(Data::TESTUSER2, "blabla");
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER2, $user->getKey());

        $user = $provider->retrieveByToken(-1, "nosuchtokenanyway");
        $this->assertEmpty($user);
    }

    public function testRetrieveByCredentials()
    {
        $provider = new UserProvider();
        $user = $provider->retrieveByCredentials(['user_email' => 'test@example.com']);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER, $user->getKey());

        $user = $provider->retrieveByCredentials(['user_email' => 'test2@example.com']);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER2, $user->getKey());

        $user = $provider->retrieveByCredentials(['user_email' => 'no such value']);
        $this->assertEmpty($user);

        $user = $provider->retrieveByCredentials(['user_nicename' => 'Test']);
        $this->assertEmpty($user);

        $user = $provider->retrieveByCredentials(['display_name' => 'Test User']);
        $this->assertEmpty($user);
    }

    public function testValidateCredentials()
    {
        $provider = new UserProvider();
        $user = $provider->retrieveByCredentials(['user_email' => 'test@example.com']);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER, $user->getKey());

        try {
            $provider->validateCredentials($user, ['user_nicename' => 'Test']);
            $this->assertFalse(true);
        }
        catch (\ErrorException $e) {
            $this->assertStringContainsString("Undefined array key", $e->getMessage());
        }

        $this->assertFalse($provider->validateCredentials($user, ['password' => 'password12']));
        $this->assertFalse($provider->validateCredentials($user, ['password' => 'assword123']));
        $this->assertTrue($provider->validateCredentials($user, ['password' => 'password123']));

        $user = $provider->retrieveByCredentials(['user_email' => 'test2@example.com']);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSER2, $user->getKey());
        $this->assertTrue($provider->validateCredentials($user, ['password' => 'SuperSecretPassword']));

        // test $wp$2y... bcrypt encrypted passwords
        $user = $provider->retrieveByCredentials(['user_email' => 'testuserpw1']);
        $this->assertNotEmpty($user);
        $this->assertEquals(Data::TESTUSERPASSWORD1, $user->getKey());
        $this->assertTrue($provider->validateCredentials($user, ['password' => 'SuperSecretPassword']));
    }
}
