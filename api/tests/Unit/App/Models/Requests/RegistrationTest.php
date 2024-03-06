<?php

namespace Tests\Unit\App\Models\Requests;

use App\Http\Controllers\Registrations\Save;
use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\WPUser;
use App\Models\Requests\Registration as RegistrationRequest;
use App\Support\Enums\PaymentOptions;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\SideEvent as SideEventData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class RegistrationTest extends TestCase
{
    private function modelsEqual(Registration $f1, Registration $f2)
    {
        $this->assertEquals($f1->getKey(), $f2->getKey());
        $this->assertEquals($f1->registration_mainevent, $f2->registration_mainevent);
        $this->assertEquals($f1->registration_event, $f2->registration_event);
        $this->assertEquals($f1->registration_fencer, $f2->registration_fencer);
        $this->assertEquals($f1->registration_payment, $f2->registration_payment);
        $this->assertEquals($f1->registration_role, $f2->registration_role);
        $this->assertEquals($f1->registration_team, $f2->registration_team);
        $this->assertEquals($f1->registration_country, $f2->registration_country);
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'registration' => $testData
        ]);
    }

    private function createRequest($testData)
    {
        $this->setRequest($testData);
        return new RegistrationRequest(new Save());
    }

    private function baseTest($testData, $user)
    {
        if (!empty($user)) {
            $this->session(['wpuser' => $user->getKey()]);
        }
        return $this->createRequest($testData)->validate(request());
    }

    public function testUpdate()
    {
        $testData = [
            'id' => RegistrationData::REG1,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT1,
            'team' => 'team-12',
            'payment' => 'G'
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        // one to check the update, one to see if the user has create powers
        $this->assertNotEmpty($model);
        $this->assertEquals(RegistrationData::REG1, $model->getKey());
        $this->assertEquals($testData['fencerId'], $model->registration_fencer);
        $this->assertEquals(Country::GER, $model->registration_country);
        $this->assertEquals(0, $model->registration_role); // null -> 0 conversion
        $this->assertEquals($testData['sideEventId'], $model->registration_event);
        $this->assertEquals($testData['team'], $model->registration_team);
        $this->assertEquals($testData['payment'], $model->registration_payment);
        $this->assertNotEmpty($model->registration_date);

        $this->modelsEqual($model, Registration::where('registration_id', $model->getKey())->first());
    }

    public function testCreate()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'countryId' => Country::GER,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertNotEmpty($model);
        $this->assertTrue($model->getKey() > 0);
        $this->assertEquals($testData['fencerId'], $model->registration_fencer);
        $this->assertEquals(Country::GER, $model->registration_country);
        $this->assertEquals(0, $model->registration_role); // null -> 0 conversion
        $this->assertEquals($testData['sideEventId'], $model->registration_event);
        $this->assertEquals($testData['team'], $model->registration_team);
        $this->assertEquals($testData['payment'], $model->registration_payment);
        $this->assertNotEmpty($model->registration_date);

        $this->modelsEqual($model, Registration::where('registration_id', $model->getKey())->first());
    }

    public function testAuthorization()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER4)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testNoAuthorizationBeforeRegistrationPeriod()
    {
        $regopens = Carbon::now()->addDays(20)->toDateString();
        $regcloses = Carbon::now()->addDays(40)->toDateString();
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_registration_open = $regopens;
        $event->event_registration_close = $regcloses;
        $event->save();

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        // HoD
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        // registrar
        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testNoAuthorizationAfterRegistrationPeriod()
    {
        $regopens = Carbon::now()->subDays(40)->toDateString();
        $regcloses = Carbon::now()->subDays(20)->toDateString();
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $event->event_registration_open = $regopens;
        $event->event_registration_close = $regcloses;
        $event->save();

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        // HoD
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        // registrar
        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testValidateFencer()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $this->setRequest($testData);
        $rules = (new RegistrationRequest(new Save()))->rules();
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['fencerId']);
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['fencerId'] = '';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['fencerId'] = 'a';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['fencerId'] = null;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes()); // fencerId must be set

        $testData['fencerId'] = FencerData::NOSUCHFENCER;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateRole()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $this->setRequest($testData);
        $rules = (new RegistrationRequest(new Save()))->rules();
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        unset($testData['roleId']);
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes()); // can be null, or left out

        $testData['roleId'] = '0';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes()); // allow '0' as a valid value

        $testData['roleId'] = 0;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes()); // allow 0 as a valid value

        $testData['roleId'] = 'a';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['roleId'] = 993882;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateSideEvent()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($testData['sideEventId']);
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        // role and sideevent cannot both be left out or null
        $this->assertFalse($validator->passes());

        $testData['sideEventId'] = null;
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        // role and sideevent cannot both be left out or null
        $this->assertFalse($validator->passes());

        $testData['roleId'] = Role::HOD;
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        $this->assertTrue($validator->passes());

        $testData['sideEventId'] = '0';
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        $this->assertFalse($validator->passes());

        $testData['sideEventId'] = 993882;
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        $this->assertFalse($validator->passes());

        $testData['sideEventId'] = 'aasads';
        $this->setRequest($testData);
        $validator = (new RegistrationRequest(new Save()))->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateTeam()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $this->setRequest($testData);
        $rules = (new RegistrationRequest(new Save()))->rules();
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['team']);
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes()); // nullable implies unset

        $testData['team'] = '';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['team'] = '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['team'] = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        // value is trimmed (starts with spaces, ends with tabs)
        $testData['team'] = '        team1                  ';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());
        $data = $validator->validated();
        $this->assertEquals('team1', $data['registration']['team']);
    }

    public function testValidatePayment()
    {
        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $this->setRequest($testData);
        $rules = (new RegistrationRequest(new Save()))->rules();
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        unset($testData['payment']);
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['payment'] = '';
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['payment'] = 0;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['payment'] = null;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['payment'] = PaymentOptions::Group;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['payment'] = PaymentOptions::Individual;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['payment'] = PaymentOptions::Organisation;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['payment'] = PaymentOptions::EVF;
        $validator = Validator::make(['registration' => $testData], $rules);
        $this->assertTrue($validator->passes());
    }
}
