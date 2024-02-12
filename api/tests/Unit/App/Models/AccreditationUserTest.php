<?php

namespace Tests\Unit\App\Models;

use App\Models\Accreditation;
use App\Models\AccreditationUser;
use App\Models\Event;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\AccreditationUser as UserData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class AccreditationUserTest extends TestCase
{
    public function testRelations()
    {
        $user = AccreditationUser::where('id', UserData::VOLUNTEER)->first();
        $this->assertNotEmpty($user);
        $this->assertEquals($user->code, $user->getAuthPassword());
        $this->assertEquals('id', $user->getAuthIdentifierName());
        $this->assertEquals(UserData::VOLUNTEER, $user->getAuthIdentifier());
        $this->assertEquals("VERSUCHER, Hans", $user->getAuthName());
        $this->assertInstanceOf(BelongsTo::class, $user->event());
        $this->assertInstanceOf(Event::class, $user->event);
        $this->assertEquals(EventData::EVENT1, $user->event->getKey());

        $this->assertInstanceOf(BelongsTo::class, $user->accreditation());
        $this->assertInstanceOf(Accreditation::class, $user->accreditation);
        $this->assertEquals(AccreditationData::VOLUNTEER, $user->accreditation->getKey());

        $user = AccreditationUser::where('id', UserData::ADMIN)->first();
        $this->assertEquals("General Code", $user->getAuthName());
    }

    public function testRoles()
    {
        // administrator: code, organiser and organisation
        $user = AccreditationUser::where('id', UserData::ADMIN)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(5, $roles);
        $this->assertContains("organiser", $roles);
        $this->assertContains("organiser:" . EventData::EVENT1, $roles);
        $this->assertContains("organisation", $roles);
        $this->assertContains("organisation:" . EventData::EVENT1, $roles);
        $this->assertContains("code", $roles);

        // checkin: code, checkin and organisation
        $user = AccreditationUser::where('id', UserData::CHECKIN)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(5, $roles);
        $this->assertContains("checkin", $roles);
        $this->assertContains("checkin:" . EventData::EVENT1, $roles);
        $this->assertContains("organisation", $roles);
        $this->assertContains("organisation:" . EventData::EVENT1, $roles);
        $this->assertContains("code", $roles);

        // user: code, accreditation and organisation
        $user = AccreditationUser::where('id', UserData::MFCAT1)->first();
        $roles = $user->getAuthRoles();
        $this->assertCount(5, $roles);
        $this->assertContains("accreditation", $roles);
        $this->assertContains("accreditation:" . EventData::EVENT1, $roles);
        $this->assertContains("organisation", $roles);
        $this->assertContains("organisation:" . EventData::EVENT1, $roles);
        $this->assertContains("code", $roles);
    }
}
