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

    public function testViewWhenRegistrationOpen()
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

    public function testViewWhenRegistrationNotYetOpen()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_registration_open = Carbon::now()->addDays(2)->toDateString();

        // a hod and superhod can see the event, but not the list of registrations
        $this->assertTrue($policy->view($superhod, $event));
        $this->assertTrue($policy->view($gerhod, $event));

        // organisation can see it always
        $this->assertTrue($policy->view($cashier, $event));
        $this->assertTrue($policy->view($accred, $event));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $event));
    }

    public function testViewWhenRegistrationClosed()
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

    public function testRegister()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();

        // hod, superhod do not have event-register powers
        $this->assertFalse($policy->register($superhod, $event));
        $this->assertFalse($policy->register($gerhod, $event));

        // organiser and registrar have powers, cashier and accred not
        $this->assertFalse($policy->register($cashier, $event));
        $this->assertFalse($policy->register($accred, $event));
        $this->assertTrue($policy->register($organiser, $event));
        $this->assertTrue($policy->register($registrar, $event));

        // unprivileged has no event-register powers
        $this->assertFalse($policy->register($unpriv, $event));
    }

    public function testAccredit()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();

        // hod, superhod do not have event-accreditation powers
        $this->assertFalse($policy->accredit($superhod, $event));
        $this->assertFalse($policy->accredit($gerhod, $event));

        // organiser and accredit have powers, cashier and registrar not
        $this->assertFalse($policy->accredit($cashier, $event));
        $this->assertTrue($policy->accredit($accred, $event));
        $this->assertTrue($policy->accredit($organiser, $event));
        $this->assertFalse($policy->accredit($registrar, $event));

        // unprivileged has no event-accreditation powers
        $this->assertFalse($policy->accredit($unpriv, $event));
    }

    public function testCashier()
    {
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        $event = Event::where('event_id', EventData::EVENT1)->first();

        // hod, superhod do not have event-cashier powers
        $this->assertFalse($policy->cashier($superhod, $event));
        $this->assertFalse($policy->cashier($gerhod, $event));

        // organiser and cashier have powers, accred and registrar not
        $this->assertTrue($policy->cashier($cashier, $event));
        $this->assertFalse($policy->cashier($accred, $event));
        $this->assertTrue($policy->cashier($organiser, $event));
        $this->assertFalse($policy->cashier($registrar, $event));

        // unprivileged has no event-cashier powers
        $this->assertFalse($policy->cashier($unpriv, $event));
    }
}
