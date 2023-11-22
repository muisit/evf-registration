<?php

namespace Tests\Unit\App\Models;

use App\Models\Competition;
use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\SideEvent;
use Tests\Support\Data\Registration as Data;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;
use DateTimeImmutable;

class RegistrationTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
    }

    public function testRelations()
    {
        $reg = Registration::where('registration_id', Data::REG1)->first();
        $this->assertNotEmpty($reg);
        $this->assertInstanceOf(BelongsTo::class, $reg->country());
        $this->assertInstanceOf(Country::class, $reg->country()->first());
        $this->assertInstanceOf(Country::class, $reg->country);

        $this->assertInstanceOf(BelongsTo::class, $reg->event());
        $this->assertInstanceOf(Event::class, $reg->event()->first());
        $this->assertInstanceOf(Event::class, $reg->event);

        $this->assertInstanceOf(BelongsTo::class, $reg->sideEvent());
        $this->assertInstanceOf(SideEvent::class, $reg->sideEvent()->first());
        $this->assertInstanceOf(SideEvent::class, $reg->sideEvent);

        $this->assertInstanceOf(BelongsTo::class, $reg->sideEvent());
        $this->assertInstanceOf(Fencer::class, $reg->fencer()->first());
        $this->assertInstanceOf(Fencer::class, $reg->fencer);

        $this->assertInstanceOf(BelongsTo::class, $reg->role());
        $this->assertEmpty($reg->role);

        $reg = Registration::where('registration_id', Data::SUP1)->first();
        $this->assertInstanceOf(Role::class, $reg->role()->first());
        $this->assertInstanceOf(Role::class, $reg->role);
    }

    public function testSave()
    {
        $reg = Registration::where('registration_id', Data::REG1)->first();
        $this->assertNotEmpty($reg);

        $newReg = new Registration();
        $newReg->registration_mainevent = $reg->registration_mainevent;
        $newReg->registration_event = $reg->registration_event;
        $newReg->registration_role = $reg->registration_role;
        $newReg->registration_fencer = $reg->registration_fencer;
        $newReg->registration_date = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $newReg->save();

        // original should be gone now
        $reg = Registration::where('registration_id', Data::REG1)->first();
        $this->assertEmpty($reg);

        $newReg2 = new Registration();
        $newReg2->registration_mainevent = $newReg->registration_mainevent;
        $newReg2->registration_event = $newReg->registration_event;
        $newReg2->registration_role = null;
        $newReg2->registration_fencer = $newReg->registration_fencer;
        $newReg2->registration_date = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $newReg2->save();

        // null is transformed to 0
        $this->assertEquals($newReg2->registration_role, 0);

        $reg = Registration::where('registration_id', $newReg->registration_id)->first();
        $this->assertEmpty($reg);
    }
}
