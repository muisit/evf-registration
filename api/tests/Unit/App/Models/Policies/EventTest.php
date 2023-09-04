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

class EventTest extends TestCase
{
    public function fixtures()
    {
        RoleData::create();
        RegistrarData::create();
    }

    public function testBefore()
    {
        $policy = new Policy();
        $admin = WPUser::where("ID", UserData::TESTUSER)->first();
        $editor = WPUser::where("ID", UserData::TESTUSER2)->first();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        // administrators can view always
        $this->assertTrue($policy->before($admin, 'view'));
        $this->assertTrue($policy->before($editor, 'view'));
        
        // admin has any imaginable policy
        $this->assertTrue($policy->before($admin, 'nosuchpolicy'));

        // a hod and superhod cannot see it
        $this->assertEmpty($policy->before($superhod, 'view'));
        $this->assertEmpty($policy->before($gerhod, 'view'));

        // organisation can see it always
        $this->assertEmpty($policy->before($cashier, 'view'));
        $this->assertEmpty($policy->before($accred, 'view'));

        // unprivileged cannot
        $this->assertEmpty($policy->before($unpriv, 'view'));
    }


    public function testViewRegistrationOpen()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();

        // a hod and superhod can see it
        $this->assertTrue($policy->view($superhod, $event));
        $this->assertTrue($policy->view($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->view($cashier, $event));
        $this->assertTrue($policy->view($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $event));
    }

    public function testViewRegistrationNotYet()
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
        $this->assertFalse($policy->view($superhod, $event));
        $this->assertFalse($policy->view($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->view($cashier, $event));
        $this->assertTrue($policy->view($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $event));
    }

    public function testViewRegistrationClosed()
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
        $this->assertTrue($policy->view($superhod, $event));
        $this->assertTrue($policy->view($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->view($cashier, $event));
        $this->assertTrue($policy->view($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $event));
    }

    public function testViewStarted()
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
        $this->assertTrue($policy->view($superhod, $event));
        $this->assertTrue($policy->view($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->view($cashier, $event));
        $this->assertTrue($policy->view($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $event));
    }

    public function testViewFinished()
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
        $this->assertFalse($policy->view($superhod, $event));
        $this->assertFalse($policy->view($gerhod, $event));

        // organisation can no longer see it
        $this->assertFalse($policy->view($cashier, $event));
        $this->assertFalse($policy->view($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $event));
    }
}
