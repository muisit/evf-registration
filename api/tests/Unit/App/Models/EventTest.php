<?php

namespace Tests\Unit\App\Support;

use App\Models\Country;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EventRole;
use App\Models\SideEvent;
use Tests\Support\Data\Event as Data;
use Tests\Support\Data\SideEvent as SideData;
use Tests\Support\Data\EventRole as EventRoleData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class EventTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
        SideData::create();
        EventRoleData::create();
    }

    public function testRelations()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();
        $this->assertNotEmpty($event);
        $this->assertInstanceOf(Event::class, $event);
        $this->assertInstanceOf(BelongsTo::class, $event->country());
        $this->assertInstanceOf(Country::class, $event->country()->first());
        $this->assertInstanceOf(Country::class, $event->country);
        $this->assertInstanceOf(BelongsTo::class, $event->type());
        $this->assertInstanceOf(EventType::class, $event->type()->first());
        $this->assertInstanceOf(EventType::class, $event->type);
        $this->assertInstanceOf(HasMany::class, $event->sides());
        $this->assertCount(6, $event->sides()->get());
        $this->assertInstanceOf(SideEvent::class, $event->sides[0]);
        $this->assertInstanceOf(HasMany::class, $event->roles());
        $this->assertCount(3, $event->roles()->get());
        $this->assertInstanceOf(EventRole::class, $event->roles[0]);
    }

    public function testRegistrationHasStarted()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();
        $now = Carbon::now();

        $this->assertTrue($event->registrationHasStarted());

        // only affected by the registration_open date
        $event->event_registration_open = $now->addDays(5)->toDateString();
        $this->assertFalse($event->registrationHasStarted());
        $event->event_registration_open = Carbon::now()->toDateString();
        $this->assertTrue($event->registrationHasStarted());

        // close has no effect
        $event->event_registration_close = Carbon::now()->subDays(10)->toDateString();
        $this->assertTrue($event->registrationHasStarted());

        // whole registration period in the past has no effect
        $event->event_registration_open = Carbon::now()->subDays(12)->toDateString();
        $this->assertTrue($event->registrationHasStarted());

        // event has passed: no effect
        $event->event_open = Carbon::now()->subDays(5)->toDateString();
        $event->event_duration = 1;
        $this->assertTrue($event->registrationHasStarted());
    }

    public function testIsOpenForRegistration()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();

        // start in past, end in future
        $this->assertTrue($event->isOpenForRegistration());

        // start in past, end in past
        $event->event_registration_close = Carbon::now()->subDays(10)->toDateString();
        $this->assertFalse($event->isOpenForRegistration());

        // start in future, end in past
        $event->event_registration_open = Carbon::now()->addDays(5)->toDateString();
        $this->assertFalse($event->isOpenForRegistration());

        // start in future, end in future
        $event->event_registration_close = Carbon::now()->addDays(8)->toDateString();
        $this->assertFalse($event->isOpenForRegistration());

        // start in past, end in future
        $event->event_registration_open = Carbon::now()->subDays(5)->toDateString();
        $this->assertTrue($event->isOpenForRegistration());

        // event_open has no effect (event in the past)
        $event->event_open = Carbon::now()->subDays(10)->toDateString();
        $event->event_duration = 1;
        $this->assertTrue($event->isOpenForRegistration());

        $event->event_duration = 20;
        $this->assertTrue($event->isOpenForRegistration());
    }

    public function testHasStarted()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();

        $this->assertFalse($event->hasStarted());

        $event->event_open = Carbon::now()->subDays(4)->toDateString();
        $event->event_duration = 8;
        $this->assertTrue($event->hasStarted());
        $event->event_duration = 5;
        $this->assertTrue($event->hasStarted());

        // event is in the past, has no effect
        $event->event_duration = 2;
        $this->assertTrue($event->hasStarted());

        // registration period in the past: no effect
        $event->event_registration_close = Carbon::now()->subDays(10)->toDateString();
        $this->assertTrue($event->hasStarted());

        // registration period in the future: no effect
        $event->event_registration_open = Carbon::now()->addDays(5)->toDateString();
        $event->event_registration_close = Carbon::now()->addDays(10)->toDateString();
        $this->assertTrue($event->hasStarted());

        $event->event_open = Carbon::now()->addDays(5)->toDateString();
        $this->assertFalse($event->hasStarted());
    }

    public function testIsFinished()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();

        $this->assertFalse($event->isFinished());

        $event->event_open = Carbon::now()->subDays(3)->toDateString();
        $this->assertFalse($event->isFinished());

        $event->event_duration = 3;
        $this->assertTrue($event->isFinished());

        $event->event_duration = 2;
        $this->assertTrue($event->isFinished());

        $event->event_open = Carbon::now()->subDays(1)->toDateString();
        $this->assertFalse($event->isFinished());

        $event->event_open = Carbon::now()->addDays(1)->toDateString();
        $this->assertFalse($event->isFinished());

        $event->event_open = Carbon::now()->subDays(5)->toDateString();
        $this->assertTrue($event->isFinished());

        // registration has no influence
        $event->event_registration_open = Carbon::now()->subDays(5)->toDateString();
        $this->assertTrue($event->isFinished());
        $event->event_registration_open = Carbon::now()->subDays(1)->toDateString();
        $this->assertTrue($event->isFinished());
        $event->event_registration_open = Carbon::now()->addDays(1)->toDateString();
        $this->assertTrue($event->isFinished());
        $event->event_registration_open = Carbon::now()->addDays(10)->toDateString();
        $this->assertTrue($event->isFinished());

        $event->event_registration_close = Carbon::now()->subDays(5)->toDateString();
        $this->assertTrue($event->isFinished());
        $event->event_registration_close = Carbon::now()->subDays(1)->toDateString();
        $this->assertTrue($event->isFinished());
        $event->event_registration_close = Carbon::now()->addDays(1)->toDateString();
        $this->assertTrue($event->isFinished());
        $event->event_registration_close = Carbon::now()->addDays(10)->toDateString();
        $this->assertTrue($event->isFinished());
    }

    public function testOverview()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();
        $overview = $event->overview();
        $this->assertTrue(is_array($overview));
        $this->assertCount(0, $overview);
    }
}
