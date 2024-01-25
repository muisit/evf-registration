<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\AccreditationTemplate;
use App\Models\WPUser;
use App\Models\Policies\AccreditationTemplate as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class AccreditationTemplateTest extends TestCase
{
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

        // organiser can see, rest cannot
        $this->assertFalse($policy->viewAny($cashier));
        $this->assertFalse($policy->viewAny($accred));
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

        $tmpl = AccreditationTemplate::where("id", TemplateData::COUNTRY)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // hods cannot view
        $this->assertFalse($policy->view($superhod, $tmpl));
        $this->assertFalse($policy->view($superhod, $tmpl));

        // organiser can view, the rest cannot
        $this->assertFalse($policy->view($cashier, $tmpl));
        $this->assertFalse($policy->view($accred, $tmpl));
        $this->assertTrue($policy->view($organiser, $tmpl));
        $this->assertFalse($policy->view($registrar, $tmpl));

        // hods cannot see
        $this->assertFalse($policy->view($gerhod, $tmpl));
        $this->assertFalse($policy->view($gerhod, $tmpl));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $tmpl));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->view($organiser, $tmpl));
        $this->assertFalse($policy->view($registrar, $tmpl));
        $this->assertFalse($policy->view($accred, $tmpl));
        $this->assertFalse($policy->view($cashier, $tmpl));

        // hods cannot see
        $this->assertFalse($policy->view($gerhod, $tmpl));
        $this->assertFalse($policy->view($gerhod, $tmpl));
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

        // organisation cannot create
        $this->assertFalse($policy->create($cashier));
        $this->assertFalse($policy->create($accred));
        $this->assertFalse($policy->create($organiser));
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

        $tmpl = AccreditationTemplate::where("id", TemplateData::COUNTRY)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // hods cannot update
        $this->assertFalse($policy->update($superhod, $tmpl));
        $this->assertFalse($policy->update($superhod, $tmpl));

        // organisater can update, rest cannot
        $this->assertFalse($policy->update($cashier, $tmpl));
        $this->assertFalse($policy->update($accred, $tmpl));
        $this->assertTrue($policy->update($organiser, $tmpl));
        $this->assertFalse($policy->update($registrar, $tmpl));

        // hods cannot
        $this->assertFalse($policy->update($gerhod, $tmpl));
        $this->assertFalse($policy->update($gerhod, $tmpl));

        // unprivileged cannot
        $this->assertFalse($policy->update($unpriv, $tmpl));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->update($organiser, $tmpl));
        $this->assertFalse($policy->update($registrar, $tmpl));

        // hods cannot
        $this->assertFalse($policy->update($gerhod, $tmpl));
        $this->assertFalse($policy->update($gerhod, $tmpl));
    }

    public function testDelete()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $tmpl = AccreditationTemplate::where("id", TemplateData::COUNTRY)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // hods cannot
        $this->assertFalse($policy->delete($superhod, $tmpl));
        $this->assertFalse($policy->delete($superhod, $tmpl));

        // organisation cannot
        $this->assertFalse($policy->delete($cashier, $tmpl));
        $this->assertFalse($policy->delete($accred, $tmpl));
        $this->assertFalse($policy->delete($organiser, $tmpl));
        $this->assertFalse($policy->delete($registrar, $tmpl));

        // hods cannot
        $this->assertFalse($policy->delete($gerhod, $tmpl));
        $this->assertFalse($policy->delete($gerhod, $tmpl));

        // unprivileged cannot
        $this->assertFalse($policy->delete($unpriv, $tmpl));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->delete($organiser, $tmpl));
        $this->assertFalse($policy->delete($registrar, $tmpl));

        // hods cannot
        $this->assertFalse($policy->delete($gerhod, $tmpl));
        $this->assertFalse($policy->delete($gerhod, $tmpl));
    }
}
