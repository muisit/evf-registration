<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\WPUser;
use App\Http\Controllers\Registrations\Pay;
use App\Models\Requests\RegistrationPay;
use App\Support\Enums\PaymentOptions;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\SideEvent as SideEventData;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Mockery;

class RegistrationPayTest extends TestCase
{
    public $authorizeCalls = [];

    private function saveRegistration()
    {
        $reg = new Registration();
        $reg->registration_mainevent = EventData::EVENT1;
        $reg->registration_country = Country::GER;
        $reg->registration_fencer = FencerData::MCAT1;
        $reg->registration_event = SideEventData::MFCAT1;
        $reg->registration_role = null;
        $reg->registration_payment = 'G';
        $reg->registration_team = null;
        $reg->save();
        return $reg;
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'payment' => $testData
        ]);
    }

    private function baseTest($testData)
    {
        $this->setRequest($testData);
        $request = new RegistrationPay(new Pay());
        return $request->validate(request());
    }

    public function testPayOrg()
    {
        $this->session(['wpuser' => UserData::TESTUSER3]);
        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'paidOrg' => 'Y',
                'paidHod' => 'Y'
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEmpty($reg2->registration_paid_hod);
        $this->assertEquals('Y', $reg2->registration_paid);

        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'paidHod' => 'Y'
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEmpty($reg2->registration_paid_hod);
        $this->assertEmpty($reg2->registration_paid);
    }

    public function testPayHod()
    {
        $this->session(['wpuser' => UserData::TESTUSERHOD]);
        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'paidOrg' => 'Y',
                'paidHod' => 'Y'
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEmpty($reg2->registration_paid); // no Org rights
        $this->assertEquals('Y', $reg2->registration_paid_hod);

        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'paidOrg' => 'Y'
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEmpty($reg2->registration_paid); // no Org rights
        $this->assertEmpty($reg2->registration_paid_hod);
    }

    public function testPayBoth()
    {
        $this->session(['wpuser' => UserData::TESTUSER]);
        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'paidOrg' => 'Y',
                'paidHod' => 'Y'
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('Y', $reg2->registration_paid_hod); // testuser has HoD rights
        $this->assertEmpty($reg2->registration_paid);

        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'paidOrg' => 'Y',
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEmpty($reg2->registration_paid_hod);
        $this->assertEquals('Y', $reg2->registration_paid); // only HoD value set in interface
    }

    public function testValidateRegistrations()
    {
        $this->session(['wpuser' => UserData::TESTUSER]);
        $regRequest = new RegistrationPay(new Pay());
        $data = [
            'registrations' => [0],
            'paidOrg' => 'Y',
            'paidHod' => 'Y'
        ];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $reg = $this->saveRegistration();
        $data['registrations'] = [$reg->getKey()];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['registrations'] = [$reg->getKey(), $reg->getKey(), $reg->getKey(), $reg->getKey()];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['registrations'][] = 0;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidatePaidHod()
    {
        $this->session(['wpuser' => UserData::TESTUSER]);
        $reg = $this->saveRegistration();
        $regRequest = new RegistrationPay(new Pay());
        $data = [
            'registrations' => [$reg->getKey()],
            'paidOrg' => 'Y',
            'paidHod' => 'Y'
        ];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['paidHod'] = 'N';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['paidHod'] = null;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['paidHod'] = 'R';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['paidHod'] = 12;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidatePaidOrg()
    {
        $this->session(['wpuser' => UserData::TESTUSER]);
        $reg = $this->saveRegistration();
        $regRequest = new RegistrationPay(new Pay());
        $data = [
            'registrations' => [$reg->getKey()],
            'paidOrg' => 'Y',
            'paidHod' => 'Y'
        ];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['paidOrg'] = 'N';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['paidOrg'] = null;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['paidOrg'] = 'R';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['paidOrg'] = 12;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());
    }
}
