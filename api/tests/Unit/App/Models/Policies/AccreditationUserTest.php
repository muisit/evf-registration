<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\Country;
use App\Models\Event;
use App\Models\AccreditationUser;
use App\Models\AccreditationDocument;
use App\Models\WPUser;
use App\Models\Policies\AccreditationUser as Policy;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationDocument as AccreditationDocumentData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class AccreditationUserTest extends TestCase
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
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accred2 = AccreditationUser::find(AccreditationUserData::ACCREDITATION);

        // administrators pass the before test
        $this->assertTrue($policy->before($admin, 'view'));
        $this->assertTrue($policy->before($admin, 'viewAny'));

        // editor is organiser AND sysop
        $this->assertTrue($policy->before($editor, 'view'));
        $this->assertTrue($policy->before($editor, 'viewAny'));

        // admin has any imaginable policy
        $this->assertTrue($policy->before($admin, 'nosuchpolicy'));
        $this->assertTrue($policy->before($editor, 'nosuchpolicy'));

        // a hod and superhod have no privileges
        $this->assertEmpty($policy->before($superhod, 'view'));
        $this->assertEmpty($policy->before($gerhod, 'view'));

        // organisation has no before privileges
        $this->assertEmpty($policy->before($cashier, 'view'));
        $this->assertEmpty($policy->before($accred, 'view'));

        // unprivileged cannot
        $this->assertEmpty($policy->before($unpriv, 'view'));

        // AccreditationUser auth scheme
        $this->assertEmpty($policy->before($admin2, 'view')); // organiser
        $this->assertEmpty($policy->before($accred2, 'view')); // accreditation
    }

    public function testViewAny()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $policy = new Policy();
        $admin = WPUser::where("ID", UserData::TESTUSER)->first();
        $editor = WPUser::where("ID", UserData::TESTUSER2)->first(); // also organiser
        $superhod = WPUser::where("ID", UserData::TESTUSERGENHOD)->first();
        $gerhod = WPUser::where("ID", UserData::TESTUSERHOD)->first();
        $unpriv = WPUser::where("ID", UserData::TESTUSER5)->first();
        $cashier = WPUser::where("ID", UserData::TESTUSER3)->first();
        $accred = WPUser::where("ID", UserData::TESTUSER4)->first();
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accred2 = AccreditationUser::find(AccreditationUserData::ACCREDITATION);

        // administrators only pass the before test
        $this->assertFalse($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($admin));

        // editors are also organisers
        $this->assertTrue($policy->viewAny($editor));
        $this->assertTrue($policy->viewAny($editor));

        // a hod and superhod have no privileges
        $this->assertFalse($policy->viewAny($superhod));
        $this->assertFalse($policy->viewAny($gerhod));

        // organisation (but not organiser) have no privileges
        $this->assertFalse($policy->viewAny($cashier));
        $this->assertFalse($policy->viewAny($accred));

        // unprivileged cannot
        $this->assertFalse($policy->viewAny($unpriv));

        // AccreditationUser auth scheme
        $this->assertTrue($policy->viewAny($admin2)); // organiser
        $this->assertFalse($policy->viewAny($accred2)); // accreditation
    }

    public function testView()
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
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accred2 = AccreditationUser::find(AccreditationUserData::ACCREDITATION);

        // administrators only pass the before test
        $this->assertFalse($policy->view($admin, $admin2));
        $this->assertFalse($policy->view($admin, $admin2));

        // editor is also an organiser
        $this->assertTrue($policy->view($editor, $admin2));
        $this->assertTrue($policy->view($editor, $admin2));

        // a hod and superhod have no privileges
        $this->assertFalse($policy->view($superhod, $admin2));
        $this->assertFalse($policy->view($gerhod, $admin2));

        // organisation which is not an organiser has no privileges
        $this->assertFalse($policy->view($cashier, $admin2));
        $this->assertFalse($policy->view($accred, $admin2));

        // unprivileged cannot
        $this->assertFalse($policy->view($unpriv, $admin2));

        // AccreditationUser auth scheme
        $this->assertTrue($policy->view($admin2, $admin2)); // organiser
        $this->assertFalse($policy->view($accred2, $admin2)); // accreditation
    }

    public function testCreate()
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
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accred2 = AccreditationUser::find(AccreditationUserData::ACCREDITATION);

        // administrators pass the before test
        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->create($admin));

        // editor is also an organiser
        $this->assertTrue($policy->create($editor));
        $this->assertTrue($policy->create($editor));

        // a hod and superhod have no privileges
        $this->assertFalse($policy->create($superhod));
        $this->assertFalse($policy->create($gerhod));

        // organisation but not organiser have no privileges
        $this->assertFalse($policy->create($cashier));
        $this->assertFalse($policy->create($accred));

        // unprivileged cannot
        $this->assertFalse($policy->create($unpriv));

        // AccreditationUser auth scheme
        $this->assertTrue($policy->create($admin2)); // organiser
        $this->assertFalse($policy->create($accred2)); // accreditation
    }

    public function testUpdate()
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
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accred2 = AccreditationUser::find(AccreditationUserData::ACCREDITATION);

        // administrators pass the before test
        $this->assertFalse($policy->update($admin, $admin2));
        $this->assertFalse($policy->update($admin, $admin2));

        // editor is also an organiser
        $this->assertTrue($policy->update($editor, $admin2));
        $this->assertTrue($policy->update($editor, $admin2));

        // a hod and superhod have no privileges
        $this->assertFalse($policy->update($superhod, $admin2));
        $this->assertFalse($policy->update($gerhod, $admin2));

        // organisation but not organiser have no privileges
        $this->assertFalse($policy->update($cashier, $admin2));
        $this->assertFalse($policy->update($accred, $admin2));

        // unprivileged cannot
        $this->assertFalse($policy->update($unpriv, $admin2));

        // AccreditationUser auth scheme
        $this->assertTrue($policy->update($admin2, $admin2)); // organiser
        $this->assertFalse($policy->update($accred2, $admin2)); // accreditation
    }

    public function testDelete()
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
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accred2 = AccreditationUser::find(AccreditationUserData::ACCREDITATION);

        // administrators pass the before test
        $this->assertFalse($policy->delete($admin, $admin2));
        $this->assertFalse($policy->delete($admin, $admin2));

        // editor is also an organiser
        $this->assertTrue($policy->delete($editor, $admin2));
        $this->assertTrue($policy->delete($editor, $admin2));

        // a hod and superhod have no privileges
        $this->assertFalse($policy->delete($superhod, $admin2));
        $this->assertFalse($policy->delete($gerhod, $admin2));

        // organisation but not organiser has no privileges
        $this->assertFalse($policy->delete($cashier, $admin2));
        $this->assertFalse($policy->delete($accred, $admin2));

        // unprivileged cannot
        $this->assertFalse($policy->delete($unpriv, $admin2));

        // AccreditationUser auth scheme
        $this->assertTrue($policy->delete($admin2, $admin2)); // organiser
        $this->assertFalse($policy->delete($accred2, $admin2)); // accreditation
    }
}
