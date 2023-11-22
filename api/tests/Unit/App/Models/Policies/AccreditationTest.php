<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Accreditation;
use App\Models\WPUser;
use App\Models\Policies\Accreditation as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class AccreditationTest extends TestCase
{
    public function fixtures()
    {
        RoleData::create();
        AccreditationData::create();
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

        // hods cannot see anything
        $this->assertFalse($policy->viewAny($superhod));
        $this->assertFalse($policy->viewAny($gerhod));

        // organiser, accreditor can see, rest cannot
        $this->assertFalse($policy->viewAny($cashier));
        $this->assertTrue($policy->viewAny($accred));
        $this->assertTrue($policy->viewAny($organiser));
        $this->assertFalse($policy->viewAny($registrar));

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
        $this->assertFalse($policy->viewAny($superhod));
        // gerHoD has mismatch in country
        $this->assertFalse($policy->viewAny($gerhod));
    }

    public function testView()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $accr = Accreditation::where("id", AccreditationData::HOD)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // hods cannot view
        $this->assertFalse($policy->view($superhod, $accr));
        $this->assertFalse($policy->view($superhod, $accr));

        // organiser, accreditor can view, the rest cannot
        $this->assertFalse($policy->view($cashier, $accr));
        $this->assertTrue($policy->view($accred, $accr));
        $this->assertTrue($policy->view($organiser, $accr));
        $this->assertFalse($policy->view($registrar, $accr));

        // hods cannot see
        $this->assertFalse($policy->view($gerhod, $accr));
        $this->assertFalse($policy->view($gerhod, $accr));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $accr));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->view($organiser, $accr));
        $this->assertFalse($policy->view($registrar, $accr));
        $this->assertFalse($policy->view($accred, $accr));
        $this->assertFalse($policy->view($cashier, $accr));

        // hods cannot see
        $this->assertFalse($policy->view($gerhod, $accr));
        $this->assertFalse($policy->view($gerhod, $accr));
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

        // hods cannot create
        $this->assertFalse($policy->create($superhod));
        $this->assertFalse($policy->create($superhod));

        // organiser and accreditor can create
        $this->assertFalse($policy->create($cashier));
        $this->assertTrue($policy->create($accred));
        $this->assertTrue($policy->create($organiser));
        $this->assertFalse($policy->create($registrar));

        // hods cannot
        $this->assertFalse($policy->create($gerhod));

        // unprivileged cannot
        $this->assertFalse($policy->create($unpriv));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->create($organiser));
        $this->assertFalse($policy->create($registrar));

        // hods cannot
        $this->assertFalse($policy->create($gerhod));
    }

    public function testUpdate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $accr = Accreditation::where("id", AccreditationData::HOD)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // hods cannot update
        $this->assertFalse($policy->update($superhod, $accr));
        $this->assertFalse($policy->update($superhod, $accr));

        // organiser, accreditor can, rest cannot
        $this->assertFalse($policy->update($cashier, $accr));
        $this->assertTrue($policy->update($accred, $accr));
        $this->assertTrue($policy->update($organiser, $accr));
        $this->assertFalse($policy->update($registrar, $accr));

        // hods cannot
        $this->assertFalse($policy->update($gerhod, $accr));
        $this->assertFalse($policy->update($gerhod, $accr));

        // unprivileged cannot
        $this->assertFalse($policy->update($unpriv, $accr));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->update($organiser, $accr));
        $this->assertFalse($policy->update($registrar, $accr));

        // hods cannot
        $this->assertFalse($policy->update($gerhod, $accr));
        $this->assertFalse($policy->update($gerhod, $accr));
    }

    public function testDelete()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $accr = Accreditation::where("id", AccreditationData::HOD)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // hods cannot
        $this->assertFalse($policy->delete($superhod, $accr));
        $this->assertFalse($policy->delete($superhod, $accr));

        // organiser, accreditor can delete
        $this->assertFalse($policy->delete($cashier, $accr));
        $this->assertTrue($policy->delete($accred, $accr));
        $this->assertTrue($policy->delete($organiser, $accr));
        $this->assertFalse($policy->delete($registrar, $accr));

        // hods cannot
        $this->assertFalse($policy->delete($gerhod, $accr));
        $this->assertFalse($policy->delete($gerhod, $accr));

        // unprivileged cannot
        $this->assertFalse($policy->delete($unpriv, $accr));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->delete($organiser, $accr));
        $this->assertFalse($policy->delete($registrar, $accr));

        // hods cannot
        $this->assertFalse($policy->delete($gerhod, $accr));
        $this->assertFalse($policy->delete($gerhod, $accr));
    }
}
