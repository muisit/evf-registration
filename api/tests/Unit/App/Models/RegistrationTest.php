<?php

namespace Tests\Unit\App\Support;

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
}
