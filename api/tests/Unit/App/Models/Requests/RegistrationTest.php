<?php

namespace Tests\Unit\App\Models\Requests;

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
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Mockery;

class RegistrationTest extends TestCase
{
    public $authorizeCalls = [];

    public function fixtures()
    {
        RegistrationData::create();
        UserData::create();
        RegistrarData::create();
        EventRoleData::create();
        SideEventData::create();
    }

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

    private function mockRequest($testData, $user)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        $stub = $this->createStub(Request::class);
        $stub->expects($this->any())->method('user')->willReturn($user);
        $map = [
            ['registration', null, $testData],
            ['eventObject', null, $event],
            ['countryObject', null, $country]
        ];
        $stub->expects($this->any())->method('get')->willReturnMap($map);
        $stub->expects($this->any())->method('all')->willReturn(['registration' => $testData]);

        $map2 = [
            // the get-all-data-from-rules call
            [['registration'], ['registration' => $testData]],
            // the check-for-two-empty-fields call
            ['registration.sideEventId', 'registration.roleId', [
                'registration' => [
                    'sideEventId' => $testData['sideEventId'] ?? null,
                    'roleId' => $testData['roleId'] ?? null
                ]
            ]],
            ['registration.roleId', 'registration.sideEventId', [
                'registration' => [
                    'sideEventId' => $testData['sideEventId'] ?? null,
                    'roleId' => $testData['roleId'] ?? null
                ]
            ]],
        ];
        $stub->expects($this->any())->method('only')->willReturnMap($map2);

        app()->singleton('request', function ($app) use ($stub) {
            return $stub;
        });
        return $stub;
    }

    private function baseTest($testData, $user)
    {
        $this->authorizeCalls = [];
        $stubController = $this->createMock(Controller::class);
        $stubController
            ->method('authorize')
            ->with(
                $this->callback(function ($arg) {
                    $this->authorizeCalls[] = $arg;
                    return true;
                }),
                $this->callback(fn($arg) => empty($arg) || $arg == Registration::class || get_class($arg) == Registration::class)
            )
            ->willReturn(true);

        $request = new RegistrationRequest($stubController);
        $stub = $this->mockRequest($testData, $user);
        return $request->validate($stub);
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
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);
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

        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);
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
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);
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
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(3, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);
        $this->assertEquals('not/ever', $this->authorizeCalls[2]);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(3, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);
        $this->assertEquals('not/ever', $this->authorizeCalls[2]);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER4)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(3, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertEquals('create', $this->authorizeCalls[1]);
        $this->assertEquals('not/ever', $this->authorizeCalls[2]);
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
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);

        // HoD
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model); // HoD is not authorized

        // registrar
        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model); // registrar is authorized
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
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);

        // HoD
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);

        // registrar
        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
    }

    public function testValidateFencer()
    {
        $stubController = $this->createMock(Controller::class);
        $regRequest = new RegistrationRequest($stubController);

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        unset($testData['fencerId']);
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['fencerId'] = '';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['fencerId'] = 'a';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['fencerId'] = null;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes()); // fencerId must be set

        $testData['fencerId'] = FencerData::NOSUCHFENCER;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());
    }

    public function testValidateRole()
    {
        $stubController = $this->createMock(Controller::class);
        $regRequest = new RegistrationRequest($stubController);

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        unset($testData['roleId']);
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes()); // can be null, or left out

        $testData['roleId'] = '0';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes()); // allow '0' as a valid value

        $testData['roleId'] = 0;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes()); // allow 0 as a valid value

        $testData['roleId'] = 'a';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['roleId'] = 993882;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());
    }

    public function testValidateSideEvent()
    {
        $stubController = $this->createMock(Controller::class);
        $regRequest = new RegistrationRequest($stubController);

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        unset($testData['sideEventId']);
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes()); // role and sideevent cannot both be left out or null

        $testData['sideEventId'] = null; // cannot both be null
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['roleId'] = Role::HOD;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        $testData['sideEventId'] = '0';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['sideEventId'] = 993882;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['sideEventId'] = 'aasads';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());
    }

    public function testValidateTeam()
    {
        $stubController = $this->createMock(Controller::class);
        $regRequest = new RegistrationRequest($stubController);

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $validator->passes();
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['team']);
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes()); // nullable implies unset

        $testData['team'] = '';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        $testData['team'] = '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['team'] = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        // value is trimmed (starts with spaces, ends with tabs)
        $testData['team'] = '        team1                  ';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());
        $data = $validator->validated();
        $this->assertEquals('team1', $data['registration']['team']);
    }

    public function testValidatePayment()
    {
        $stubController = $this->createMock(Controller::class);
        $regRequest = new RegistrationRequest($stubController);

        $testData = [
            'id' => 0,
            'fencerId' => FencerData::MCAT1,
            'roleId' => null,
            'sideEventId' => SideEventData::MFCAT2,
            'team' => null,
            'payment' => 'G'
        ];
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        unset($testData['payment']);
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['payment'] = '';
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['payment'] = 0;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['payment'] = null;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertFalse($validator->passes());

        $testData['payment'] = PaymentOptions::Group;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        $testData['payment'] = PaymentOptions::Individual;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        $testData['payment'] = PaymentOptions::Organisation;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());

        $testData['payment'] = PaymentOptions::EVF;
        $request = $this->mockRequest($testData, null);
        $validator = $regRequest->createValidator($request);
        $this->assertTrue($validator->passes());
    }
}
