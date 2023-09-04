<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Event;
use App\Models\WPUser;
use App\Models\Policies\Event as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

/**
 * Special test case for the viewRegistration policy
 */
class EventRegistrationTest extends TestCase
{
    public function fixtures()
    {
        RoleData::create();
        RegistrarData::create();
    }

    public function testViewRegistrationWhenRegistrationOpen()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();

        // a hod and superhod can see it
        $this->assertTrue($policy->viewRegistrations($superhod, $event));
        $this->assertTrue($policy->viewRegistrations($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->viewRegistrations($cashier, $event));
        $this->assertTrue($policy->viewRegistrations($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->viewRegistrations($unpriv, $event));
    }

    public function testViewRegistrationWhenRegistrationNotYetOpen()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_registration_open = Carbon::now()->addDays(2)->toDateString();

        // a hod and superhod cannot see it
        $this->assertFalse($policy->viewRegistrations($superhod, $event));
        $this->assertFalse($policy->viewRegistrations($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->viewRegistrations($cashier, $event));
        $this->assertTrue($policy->viewRegistrations($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->viewRegistrations($unpriv, $event));
    }

    public function testViewRegistrationWhenRegistrationClosed()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_registration_close = Carbon::now()->subDays(2)->toDateString();

        // a hod and superhod can see it
        $this->assertTrue($policy->viewRegistrations($superhod, $event));
        $this->assertTrue($policy->viewRegistrations($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->viewRegistrations($cashier, $event));
        $this->assertTrue($policy->viewRegistrations($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->viewRegistrations($unpriv, $event));
    }

    public function testViewRegistrationWhenStarted()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_open = Carbon::now()->subDays(2)->toDateString();
        $event->event_duration = 10;

        // a hod and superhod can see it
        $this->assertTrue($policy->viewRegistrations($superhod, $event));
        $this->assertTrue($policy->viewRegistrations($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->viewRegistrations($cashier, $event));
        $this->assertTrue($policy->viewRegistrations($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->viewRegistrations($unpriv, $event));
    }

    public function testViewRegistrationWhenFinished()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_open = Carbon::now()->subDays(10)->toDateString();
        $event->event_duration = 1;

        // a hod and superhod cannot see it
        $this->assertFalse($policy->viewRegistrations($superhod, $event));
        $this->assertFalse($policy->viewRegistrations($gerhod, $event));

        // organisation can no longer see it
        $this->assertFalse($policy->viewRegistrations($cashier, $event));
        $this->assertFalse($policy->viewRegistrations($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->viewRegistrations($unpriv, $event));
    }
}
