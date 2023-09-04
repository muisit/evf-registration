<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Me as Schema;
use App\Models\Event;
use App\Models\WPUser;
use App\Models\Country;
use App\Models\EventRole;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Unit\TestCase;

class MeTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        EventData::create();
        RoleData::create();
    }

    public function testEmpty()
    {
        $this->session([]);
        $schema = new Schema();
        $this->assertFalse($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertEmpty($schema->username);
        $this->assertEmpty($schema->credentials);

        $schema = new Schema(new Country());
        $this->assertFalse($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertEmpty($schema->username);
        $this->assertEmpty($schema->credentials);
    }

    public function testCreate()
    {
        $this->session([]);
        $schema = new Schema(WPUser::where('ID', UserData::TESTUSER)->first());
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(2, $schema->credentials); // user, sysop
    }

    public function testRolesForEvent()
    {
        $user = WPUser::where('ID', UserData::TESTUSER4)->first();
        $this->session([]);
        $schema = new Schema($user, Event::where('event_id', EventData::EVENT1)->first());
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(3, $schema->credentials);

        $newRole = new EventRole();
        $newRole->event_id = EventData::EVENT1 + 1;
        $newRole->user_id = UserData::TESTUSER4;
        $newRole->role_type = 'registrar';
        $newRole->save();
        $roles = $user->getAuthRoles();
        $this->assertCount(5, $roles);

        $this->session([]);
        $schema = new Schema($user, Event::where('event_id', EventData::EVENT1)->first());
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(3, $schema->credentials); // only the event roles

        $this->session([]);
        $schema = new Schema($user);
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(5, $schema->credentials); // all roles
    }
}
