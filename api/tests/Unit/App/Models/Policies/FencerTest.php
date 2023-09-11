<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Fencer;
use App\Models\WPUser;
use App\Models\Policies\Fencer as Policy;
use Tests\Support\Data\EventRole as RoleData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\WPUser as UserData;
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
        $policy = new Policy();
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $organiser = WPUser::where("ID", UserData::TESTUSERORGANISER)->first();
        $registrar = WPUser::where("ID", UserData::TESTUSERREGISTRAR)->first();

        // a superhod cannot 'just' see any fencer, it requires a country object
        $this->assertFalse($policy->viewAny($superhod));

        // organiser and registrar can see any fencer
        $this->assertFalse($policy->viewAny($cashier));
        $this->assertFalse($policy->viewAny($accred));
        $this->assertTrue($policy->viewAny($organiser));
        $this->assertTrue($policy->viewAny($registrar));

        // unprivileged cannot
        $this->assertFalse($policy->viewAny($unpriv));
    }

    public function testView()
    {
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

        // organiser and registrar can see any individual fencer
        $this->assertFalse($policy->view($cashier, $fencerGER));
        $this->assertFalse($policy->view($accred, $fencerITA));
        $this->assertTrue($policy->view($organiser, $fencerGER));
        $this->assertTrue($policy->view($registrar, $fencerITA));

        // gerhod can only see ger fencers
        $this->assertTrue($policy->view($gerhod, $fencerGER));
        $this->assertFalse($policy->view($gerhod, $fencerITA));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $fencerGER));
    }
}
