<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Me as Schema;
use App\Models\Event;
use App\Models\WPUser;
use App\Models\Country;
use App\Models\EventRole;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Unit\TestCase;

class MeTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        EventData::create();
        RoleData::create();
        RegistrarData::create();
    }

    public function testEmpty()
    {
        $this->session([]);
        $schema = new Schema();
        $this->assertFalse($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertEmpty($schema->username);
        $this->assertEmpty($schema->credentials);
        $this->assertEmpty($schema->countryId);

        $schema = new Schema(new Country());
        $this->assertFalse($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertEmpty($schema->username);
        $this->assertEmpty($schema->credentials);
        $this->assertEmpty($schema->countryId);
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
        $this->assertEmpty($schema->countryId);
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
        $this->assertCount(3, $schema->credentials); // user, organisation:1, accreditation:1
        $this->assertEmpty($schema->countryId);

        $newRole = new EventRole();
        $newRole->event_id = EventData::EVENT1 + 1;
        $newRole->user_id = UserData::TESTUSER4;
        $newRole->role_type = 'registrar';
        $newRole->save();
        $roles = $user->getAuthRoles();
        $this->assertCount(5, $roles); // user, organisation:1, organisation:2, accreditation:1, registrar:2

        $this->session([]);
        $schema = new Schema($user, Event::where('event_id', EventData::EVENT1)->first());
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(3, $schema->credentials); // user, organisation:1, accreditation:1

        $this->session([]);
        $schema = new Schema($user);
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(5, $schema->credentials); // all roles
    }

    public function testHodRoles()
    {
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $this->session([]);
        $schema = new Schema($user, Event::where('event_id', EventData::EVENT1)->first());
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(3, $schema->credentials); // user, hod, hod:12
        $this->assertContains("hod:12", $schema->credentials);
        $this->assertNotEmpty($schema->countryId);
        $this->assertEquals(Country::GER, $schema->countryId);
    }

    public function testSuperHodRoles()
    {
        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $this->session([]);
        $schema = new Schema($user, Event::where('event_id', EventData::EVENT1)->first());
        $this->assertTrue($schema->status);
        $this->assertNotEmpty($schema->token);
        $this->assertNotEmpty($schema->username);
        $this->assertNotEmpty($schema->credentials);
        $this->assertCount(3, $schema->credentials); // user, hod, superhod
        $this->assertContains("superhod", $schema->credentials);
        $this->assertEmpty($schema->countryId);
    }
}
