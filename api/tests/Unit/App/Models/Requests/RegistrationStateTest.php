<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Registration;
use App\Http\Controllers\Registrations\State;
use App\Models\Requests\RegistrationState;
use App\Support\Enums\PaymentOptions;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\AccreditationUser as UserData;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class RegistrationStateTest extends TestCase
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
            'state' => $testData
        ]);
    }

    private function baseTest($testData)
    {
        $this->setRequest($testData);
        $request = new RegistrationState(new State());
        return $request->validate(request());
    }

    public function testHappyFlow()
    {
        $this->session(['accreditationuser' => UserData::ACCREDITATION]);
        $reg = $this->saveRegistration();
        $model = $this->baseTest(
            [
                'registrations' => [$reg->registration_id],
                'value' => 'P',
                'previous' => 'R'
            ]
        );
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('P', $reg2->registration_state);
    }

    public function testStateSwitches()
    {
        $this->session(['accreditationuser' => UserData::ACCREDITATION]);
        $reg = $this->saveRegistration();
        $data = [
            'registrations' => [$reg->registration_id],
            'value' => 'A',
            'previous' => 'R'
        ];
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('A', $reg2->registration_state);

        $data['value'] = 'P';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('A', $reg2->registration_state); // not changed, previous does not match

        $data['previous'] = 'A';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('P', $reg2->registration_state);

        $data['value'] = 'R';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('P', $reg2->registration_state); // not changed, previous does not match

        $data['previous'] = 'P';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals(null, $reg2->registration_state);

        // if previous is null, always change the state
        $data['previous'] = null;
        $data['value'] = 'P';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('P', $reg2->registration_state);

        $data['value'] = 'A';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals('A', $reg2->registration_state);

        $data['value'] = 'R';
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);
        $reg2 = Registration::find($reg->getKey());
        $this->assertNotEmpty($reg2);
        $this->assertEquals(null, $reg2->registration_state);
    }

    public function testAuthorization()
    {
        $this->session(['accreditationuser' => UserData::ACCREDITATION]);
        $reg = $this->saveRegistration();
        $data = [
            'registrations' => [$reg->registration_id],
            'value' => 'A',
        ];
        $model = $this->baseTest($data);
        $this->assertNotEmpty($model);

        $this->assertException(function () use ($reg) {
            $this->resetApplication();
            $this->session(['accreditationuser' => UserData::ADMIN]);
            $data = [
                'registrations' => [$reg->registration_id],
                'value' => 'A',
            ];
            $model = $this->baseTest($data);
        }, AuthorizationException::class);

        $this->assertException(function () use ($reg) {
            $this->resetApplication();
            $this->session(['accreditationuser' => UserData::CHECKIN]);
            $data = [
                'registrations' => [$reg->registration_id],
                'value' => 'A',
            ];
            $model = $this->baseTest($data);
        }, AuthorizationException::class);

        $this->assertException(function () use ($reg) {
            $this->resetApplication();
            $this->session(['accreditationuser' => UserData::CHECKOUT]);
            $data = [
                'registrations' => [$reg->registration_id],
                'value' => 'A',
            ];
            $model = $this->baseTest($data);
        }, AuthorizationException::class);

        $this->assertException(function () use ($reg) {
            $this->resetApplication();
            $this->session(['accreditationuser' => UserData::DT]);
            $data = [
                'registrations' => [$reg->registration_id],
                'value' => 'A',
            ];
            $model = $this->baseTest($data);
        }, AuthorizationException::class);

        $this->assertException(function () use ($reg) {
            $this->resetApplication();
            $this->session(['accreditationuser' => UserData::NOSUCHID]);
            $data = [
                'registrations' => [$reg->registration_id],
                'value' => 'A',
            ];
            $model = $this->baseTest($data);
        }, AuthorizationException::class);
    }

    public function testValidateRegistrations()
    {
        $this->session(['accreditationuser' => UserData::ACCREDITATION]);
        $regRequest = new RegistrationState(new State());
        $reg = $this->saveRegistration();
        $data = [
            'registrations' => [$reg->registration_id],
            'value' => 'A',
            'previous' => 'R'
        ];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $reg = $this->saveRegistration();
        $data['registrations'] = [0];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['registrations'] = [$reg->getKey(), $reg->getKey(), $reg->getKey(), $reg->getKey()];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['registrations'][] = 0;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateValue()
    {
        $this->session(['accreditationuser' => UserData::ACCREDITATION]);
        $regRequest = new RegistrationState(new State());
        $reg = $this->saveRegistration();
        $data = [
            'registrations' => [$reg->registration_id],
            'value' => 'A',
            'previous' => 'R'
        ];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['value'] = 'P';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['value'] = 'R';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['value'] = 'N';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['value'] = null;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['value'] = 12;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidatePrevious()
    {
        $this->session(['accreditationuser' => UserData::ACCREDITATION]);
        $regRequest = new RegistrationState(new State());
        $reg = $this->saveRegistration();
        $data = [
            'registrations' => [$reg->registration_id],
            'value' => 'A',
            'previous' => 'A'
        ];
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['previous'] = 'P';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['previous'] = 'R';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['previous'] = null;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['previous']);
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['previous'] = 'N';
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['previous'] = 12;
        $this->setRequest($data);
        $validator = $regRequest->createValidator(request());
        $this->assertFalse($validator->passes());
    }
}
