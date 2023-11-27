<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\WPUser;
use App\Models\Policies\Registration as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class RegistrationTest extends TestCase
{
    public function fixtures()
    {
        RoleData::create();
        RegistrarData::create();
        RegistrationData::create();
    }

    public function testBefore()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

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
        $this->assertTrue($policy->before($admin, 'viewAny'));
        $this->assertTrue($policy->before($editor, 'viewAny'));
        
        // admin has any imaginable policy
        $this->assertTrue($policy->before($admin, 'nosuchpolicy'));

        // a hod and superhod cannot see it
        $this->assertEmpty($policy->before($superhod, 'view'));
        $this->assertEmpty($policy->before($gerhod, 'view'));

        // organisation cannot see it due to before
        $this->assertEmpty($policy->before($cashier, 'view'));
        $this->assertEmpty($policy->before($accred, 'view'));

        // unprivileged cannot
        $this->assertEmpty($policy->before($unpriv, 'view'));
    }

    public function testViewAny()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod or hod cam see all registrations (only for a specific country)
        $this->assertTrue($policy->viewAny($superhod));
        $this->assertTrue($policy->viewAny($gerhod));

        // organiser, registrar, accreditor and cashier can see any registration (for the current event)
        $this->assertTrue($policy->viewAny($cashier));
        $this->assertTrue($policy->viewAny($accred));
        $this->assertTrue($policy->viewAny($organiser));
        $this->assertTrue($policy->viewAny($registrar));

        // unprivileged cannot
        $this->assertFalse($policy->viewAny($unpriv));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->viewAny($organiser));
        $this->assertFalse($policy->viewAny($registrar));
        $this->assertFalse($policy->viewAny($accred));
        $this->assertFalse($policy->viewAny($cashier));

        // no changes for SuperHoD
        $this->assertTrue($policy->viewAny($superhod));
        // gerHoD has mismatch in country
        $this->assertFalse($policy->viewAny($gerhod));
    }

    public function testView()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $regGER = Registration::where("registration_id", RegistrationData::REG1)->first();
        $regITA = Registration::where("registration_id", RegistrationData::TEAM1)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can see any individual registration
        $this->assertTrue($policy->view($superhod, $regGER));
        $this->assertTrue($policy->view($superhod, $regITA));

        // organiser, registrar, accreditor and cashier can see any individual registration
        $this->assertTrue($policy->view($cashier, $regGER));
        $this->assertTrue($policy->view($accred, $regITA));
        $this->assertTrue($policy->view($organiser, $regGER));
        $this->assertTrue($policy->view($registrar, $regITA));

        // gerhod can only see ger registrations
        $this->assertTrue($policy->view($gerhod, $regGER));
        $this->assertFalse($policy->view($gerhod, $regITA));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $regITA));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->view($organiser, $regGER));
        $this->assertFalse($policy->view($registrar, $regGER));
        $this->assertFalse($policy->view($accred, $regGER));
        $this->assertFalse($policy->view($cashier, $regGER));

        // if country object does not match HoD, check fails
        $this->assertFalse($policy->view($gerhod, $regGER));
        $this->assertFalse($policy->view($gerhod, $regITA));
    }

    public function testCreate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can create registrations
        $this->assertTrue($policy->create($superhod));
        $this->assertTrue($policy->create($superhod));

        // organiser and registrar can create registrations
        $this->assertFalse($policy->create($cashier));
        $this->assertFalse($policy->create($accred));
        $this->assertTrue($policy->create($organiser));
        $this->assertTrue($policy->create($registrar));

        // gerhod can create fencers
        $this->assertTrue($policy->create($gerhod));

        // unprivileged cannot
        $this->assertFalse($policy->create($unpriv));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->create($organiser));
        $this->assertFalse($policy->create($registrar));

        // if country object does not match HoD, check fails
        $this->assertFalse($policy->create($gerhod));
    }

    public function testUpdate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $regGER = Registration::where("registration_id", RegistrationData::REG1)->first();
        $regITA = Registration::where("registration_id", RegistrationData::TEAM1)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can update any individual registration
        $this->assertTrue($policy->update($superhod, $regGER));
        $this->assertTrue($policy->update($superhod, $regITA));

        // organiser, registrar, accreditor and cashier can update any individual registration
        $this->assertTrue($policy->update($cashier, $regGER));
        $this->assertTrue($policy->update($accred, $regITA));
        $this->assertTrue($policy->update($organiser, $regGER));
        $this->assertTrue($policy->update($registrar, $regITA));

        // gerhod can only update ger registrations
        $this->assertTrue($policy->update($gerhod, $regGER));
        $this->assertFalse($policy->update($gerhod, $regITA));

        // unprivileged cannot
        $this->assertFalse($policy->update($unpriv, $regGER));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->update($organiser, $regGER));
        $this->assertFalse($policy->update($registrar, $regITA));

        // if country object does not match HoD, check fails
        $this->assertFalse($policy->update($gerhod, $regGER));
        $this->assertFalse($policy->update($gerhod, $regITA));
    }

    public function testDelete()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $regGER = Registration::where("registration_id", RegistrationData::REG1)->first();
        $regITA = Registration::where("registration_id", RegistrationData::TEAM1)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can delete any individual registration
        $this->assertTrue($policy->delete($superhod, $regGER));
        $this->assertTrue($policy->delete($superhod, $regITA));

        // organiser, registrarr can delete any individual registration
        $this->assertFalse($policy->delete($cashier, $regGER));
        $this->assertFalse($policy->delete($accred, $regITA));
        $this->assertTrue($policy->delete($organiser, $regGER));
        $this->assertTrue($policy->delete($registrar, $regITA));

        // gerhod can only delete ger registrations
        $this->assertTrue($policy->delete($gerhod, $regGER));
        $this->assertFalse($policy->delete($gerhod, $regITA));

        // unprivileged cannot
        $this->assertFalse($policy->delete($unpriv, $regGER));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->delete($organiser, $regGER));
        $this->assertFalse($policy->delete($registrar, $regITA));

        // if country object does not match HoD, check fails
        $this->assertFalse($policy->delete($gerhod, $regGER));
        $this->assertFalse($policy->delete($gerhod, $regITA));
    }

    public function testCashierAccreditor()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod is not a cashier or accreditor
        $this->assertFalse($policy->cashier($superhod));
        $this->assertFalse($policy->accredit($superhod));

        // organiser can do all
        $this->assertTrue($policy->cashier($cashier));
        $this->assertFalse($policy->cashier($accred));
        $this->assertTrue($policy->cashier($organiser));
        $this->assertFalse($policy->cashier($registrar));

        $this->assertFalse($policy->accredit($cashier));
        $this->assertTrue($policy->accredit($accred));
        $this->assertTrue($policy->accredit($organiser));
        $this->assertFalse($policy->accredit($registrar));

        // gerhod is not a cashier or accreditor
        $this->assertFalse($policy->cashier($gerhod));
        $this->assertFalse($policy->accredit($gerhod));

        // unprivileged cannot
        $this->assertFalse($policy->cashier($unpriv));
        $this->assertFalse($policy->accredit($unpriv));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->cashier($cashier));
        $this->assertFalse($policy->cashier($accred));
        $this->assertFalse($policy->cashier($organiser));
        $this->assertFalse($policy->cashier($registrar));

        $this->assertFalse($policy->accredit($cashier));
        $this->assertFalse($policy->accredit($accred));
        $this->assertFalse($policy->accredit($organiser));
        $this->assertFalse($policy->accredit($registrar));
    }
}
