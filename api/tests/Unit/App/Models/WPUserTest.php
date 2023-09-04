<?php

namespace Tests\Unit\App\Models;

use App\Models\Country;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\WPUser;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\WPUser as Data;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Unit\TestCase;

class WPUserTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
        RegistrarData::create();
        EventRoleData::create();
    }

    public function testRelations()
    {
        $user = WPUser::where('id', Data::TESTUSER)->first();
        $this->assertNotEmpty($user);
        $this->assertNotEmpty($user->getAuthPassword());
        $this->assertEquals('ID', $user->getAuthIdentifierName());
        $this->assertEquals(Data::TESTUSER, $user->getAuthIdentifier());
        $this->assertEquals("Test User", $user->getAuthName());
    }

    public function testRoles()
    {
        // administrator, only user and sysop
        $user = WPUser::where('id', Data::TESTUSER)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(2, $roles);
        $this->assertContains("sysop", $roles);
        $this->assertContains("user", $roles);

        // editor, which is a user and a sysop, an organiser and organisation
        $user = WPUser::where('id', Data::TESTUSER2)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(4, $roles);
        $this->assertContains("user", $roles);
        $this->assertContains("sysop", $roles);
        $this->assertContains("organisation:" . EventData::EVENT1, $roles);
        $this->assertContains("organiser:" . EventData::EVENT1, $roles);

        $user = WPUser::where('id', Data::TESTUSER3)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(3, $roles);
        $this->assertContains("user", $roles);
        $this->assertContains("organisation:" . EventData::EVENT1, $roles);
        $this->assertContains("cashier:" . EventData::EVENT1, $roles);

        $user = WPUser::where('id', Data::TESTUSERGENHOD)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(3, $roles);
        $this->assertContains("user", $roles);
        $this->assertContains("hod", $roles);
        $this->assertContains("superhod", $roles);

        $user = WPUser::where('id', Data::TESTUSERHOD)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(3, $roles);
        $this->assertContains("user", $roles);
        $this->assertContains("hod", $roles);
        $this->assertContains("hod:" . Country::GER, $roles);

        $user = WPUser::where('id', Data::TESTUSER4)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(3, $roles);
        $this->assertContains("user", $roles);
        $this->assertContains("organisation:" . EventData::EVENT1, $roles);
        $this->assertContains("accreditation:" . EventData::EVENT1, $roles);

        $newRole = new EventRole();
        $newRole->event_id = EventData::EVENT1 + 1;
        $newRole->user_id = Data::TESTUSER4;
        $newRole->role_type = 'registrar';
        $newRole->save();
        $roles = $user->getAuthRoles();
        $this->assertCount(5, $roles);
        $this->assertContains("organisation:" . (EventData::EVENT1 + 1), $roles);
        $this->assertContains("registrar:" . (EventData::EVENT1 + 1), $roles);

        $roles = $user->getAuthRoles(Event::where('event_id', EventData::EVENT1)->first());
        $this->assertCount(3, $roles);
        $this->assertNotContains("registrar:" . (EventData::EVENT1 + 1), $roles);
    }
}
