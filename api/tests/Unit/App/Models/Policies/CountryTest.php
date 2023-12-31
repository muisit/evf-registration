<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Country;
use App\Models\Event;
use App\Models\WPUser;
use App\Models\Policies\Country as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class CountryTest extends TestCase
{
    public function fixtures()
    {
        RoleData::create();
        RegistrarData::create();
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

        // a superhod has no before policy
        $this->assertEmpty($policy->before($superhod, 'view'));
        $this->assertEmpty($policy->before($gerhod, 'view'));

        // organisation cannot see it due to before
        $this->assertEmpty($policy->before($cashier, 'view'));
        $this->assertEmpty($policy->before($accred, 'view'));

        // unprivileged cannot
        $this->assertEmpty($policy->before($unpriv, 'view'));
    }

    public function testView()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $countryGER = Country::where("country_id", Country::GER)->first();
        $countryITA = Country::where("country_id", Country::ITA)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can see any country
        $this->assertTrue($policy->view($superhod, $countryGER));
        $this->assertTrue($policy->view($superhod, $countryITA));

        // organiser and registrar can see countries
        $this->assertFalse($policy->view($cashier, $countryGER));
        $this->assertFalse($policy->view($accred, $countryITA));
        $this->assertTrue($policy->view($organiser, $countryGER));
        $this->assertTrue($policy->view($registrar, $countryITA));

        // gerhod can only see ger
        $this->assertTrue($policy->view($gerhod, $countryGER));
        $this->assertFalse($policy->view($gerhod, $countryITA));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $countryGER));
    }

    public function testInvalidEvent()
    {
        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $countryGER = Country::where("country_id", Country::GER)->first();
        $countryITA = Country::where("country_id", Country::ITA)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // organiser and registrar are no longer recognised as organisers
        $this->assertFalse($policy->view($cashier, $countryGER));
        $this->assertFalse($policy->view($accred, $countryITA));
        $this->assertFalse($policy->view($organiser, $countryGER));
        $this->assertFalse($policy->view($registrar, $countryITA));

        // request object for country has no effect on policy
        $this->assertTrue($policy->view($gerhod, $countryGER));
        $this->assertFalse($policy->view($gerhod, $countryITA));
    }

    public function testHod()
    {
        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $countryGER = Country::where("country_id", Country::GER)->first();
        $countryITA = Country::where("country_id", Country::ITA)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // organiser and registrar are no longer recognised as organisers
        $this->assertFalse($policy->hod($cashier, $countryGER));
        $this->assertFalse($policy->hod($accred, $countryITA));
        $this->assertFalse($policy->hod($organiser, $countryGER));
        $this->assertFalse($policy->hod($registrar, $countryITA));

        $this->assertTrue($policy->hod($gerhod, $countryGER));
        $this->assertFalse($policy->hod($gerhod, $countryITA));

        // super hod is hod for everyone
        $this->assertTrue($policy->hod($superhod, $countryGER));
        $this->assertTrue($policy->hod($superhod, $countryITA));

        $this->assertFalse($policy->hod($unpriv, $countryITA));
    }

}
