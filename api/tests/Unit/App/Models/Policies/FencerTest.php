<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Models\Policies\Fencer as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class FencerTest extends TestCase
{
    public function fixtures()
    {
        RoleData::create();
        RegistrarData::create();
        FencerData::create();
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

        // a superhod or hod cannot see all fencers
        $this->assertFalse($policy->viewAny($superhod));
        $this->assertFalse($policy->viewAny($gerhod));

        // organiser, registrar, accreditor can see any fencer
        $this->assertFalse($policy->viewAny($cashier));
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

        // no changes for HoD
        $this->assertFalse($policy->viewAny($superhod));
        $this->assertFalse($policy->viewAny($gerhod));
    }

    public function testView()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $fencerGER = Fencer::where("fencer_id", FencerData::MCAT1)->first();
        $fencerITA = Fencer::where("fencer_id", FencerData::MCAT2)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can see any individual fencer
        $this->assertTrue($policy->view($superhod, $fencerGER));
        $this->assertTrue($policy->view($superhod, $fencerITA));

        // organiser, registrar, accreditor can see any individual fencer
        $this->assertFalse($policy->view($cashier, $fencerGER));
        $this->assertTrue($policy->view($accred, $fencerITA));
        $this->assertTrue($policy->view($organiser, $fencerGER));
        $this->assertTrue($policy->view($registrar, $fencerITA));

        // gerhod can only see ger fencers
        $this->assertTrue($policy->view($gerhod, $fencerGER));
        $this->assertFalse($policy->view($gerhod, $fencerITA));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $fencerGER));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->view($organiser, $fencerGER));
        $this->assertFalse($policy->view($registrar, $fencerITA));

        // if country object does not match HoD, check fails
        $this->assertFalse($policy->view($gerhod, $fencerGER));
        $this->assertFalse($policy->view($gerhod, $fencerITA));
    }

    public function testCreate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $fencerGER = Fencer::where("fencer_id", FencerData::MCAT1)->first();
        $fencerITA = Fencer::where("fencer_id", FencerData::MCAT2)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can create fencers
        $this->assertTrue($policy->create($superhod));
        $this->assertTrue($policy->create($superhod));

        // organiser and registrar can create fencers
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

        $fencerGER = Fencer::where("fencer_id", FencerData::MCAT1)->first();
        $fencerITA = Fencer::where("fencer_id", FencerData::MCAT2)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod can update any individual fencer
        $this->assertTrue($policy->update($superhod, $fencerGER));
        $this->assertTrue($policy->update($superhod, $fencerITA));

        // organiser and registrar can update any individual fencer
        $this->assertFalse($policy->update($cashier, $fencerGER));
        $this->assertFalse($policy->update($accred, $fencerITA));
        $this->assertTrue($policy->update($organiser, $fencerGER));
        $this->assertTrue($policy->update($registrar, $fencerITA));

        // gerhod can only update ger fencers
        $this->assertTrue($policy->update($gerhod, $fencerGER));
        $this->assertFalse($policy->update($gerhod, $fencerITA));

        // unprivileged cannot
        $this->assertFalse($policy->update($unpriv, $fencerGER));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->update($organiser, $fencerGER));
        $this->assertFalse($policy->update($registrar, $fencerITA));

        // if country object does not match HoD, check fails
        $this->assertFalse($policy->update($gerhod, $fencerGER));
        $this->assertFalse($policy->update($gerhod, $fencerITA));
    }

    public function testPictureState()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $fencerGER = Fencer::where("fencer_id", FencerData::MCAT1)->first();
        $fencerITA = Fencer::where("fencer_id", FencerData::MCAT2)->first();

        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod cannot update picture states
        $this->assertFalse($policy->pictureState($superhod));

        // organiser and registrar can update picture states
        $this->assertFalse($policy->pictureState($cashier));
        $this->assertFalse($policy->pictureState($accred));
        $this->assertTrue($policy->pictureState($organiser));
        $this->assertTrue($policy->pictureState($registrar));

        // gerhod can not update picture states
        $this->assertFalse($policy->pictureState($gerhod));

        // unprivileged cannot
        $this->assertFalse($policy->pictureState($unpriv));

        request()->merge([
            'eventObject' => null,
            'countryObject' => Country::where('country_id', Country::ITA)->first()
        ]);
        // organisation is no longer recognised as such
        $this->assertFalse($policy->pictureState($organiser));
        $this->assertFalse($policy->pictureState($registrar));
    }
}
