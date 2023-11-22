<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\WPUser;
use App\Models\Requests\RegistrationDelete;
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

class RegistrationDeleteTest extends TestCase
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

        $request = new RegistrationDelete($stubController);
        $stub = $this->mockRequest($testData, $user);
        return $request->validate($stub);
    }

    public function testDelete()
    {
        $testData = $this->saveRegistration();
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest(['id' => $testData->registration_id], $user);

        // one to check the delete
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('delete', $this->authorizeCalls[0]);
        $this->assertNotEmpty($model);
        $this->assertEmpty(Registration::find($model->getKey()));
    }

    public function testAuthorization()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('delete', $this->authorizeCalls[0]);
        $this->assertEmpty(Registration::find($model->getKey()));

        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('delete', $this->authorizeCalls[0]);
        $this->assertEmpty(Registration::find($model->getKey()));

        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('delete', $this->authorizeCalls[0]);
        $this->assertEmpty(Registration::find($model->getKey()));

        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('delete', $this->authorizeCalls[0]);
        $this->assertEmpty(Registration::find($model->getKey()));
    }

    public function testUnauthorized()
    {
        // we mock the controller authorize call to always return true,
        // so we cannot test this properly here
        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
    }
}
