<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\WPUser;
use App\Models\Requests\EventRole as EventRoleRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\EventRole as EventRoleData;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Mockery;

class EventRoleTest extends TestCase
{
    public $authorizeCalls = [];

    private function testData()
    {
        return [[
            'id' => EventRoleData::ORGANISER,
            'userId' => UserData::TESTUSERGENHOD,
            'role' => 'organiser'
        ]];
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
                $this->callback(fn($arg) => empty($arg) || $arg == Event::class || get_class($arg) == Event::class)
            )
            ->willReturn(true);

        $request = new EventRoleRequest($stubController);

        $stub = $this->createMock(Request::class);
        $stub->expects($this->any())->method('user')->willReturn($user);
        $stub->expects($this->any())->method('get')->with('roles')->willReturn($testData);
        $stub->expects($this->any())->method('all')->willReturn(['roles' => $testData]);
        $stub->expects($this->any())->method('only')->willReturn(['roles' => $testData]);
        return $request->validate($stub);
    }

    public function testUpdate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
        $this->assertNotEmpty($model); // event object
    }

    public function testAuthorization()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
    }

    public function testValidateId()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRoleRequest($stubController))->rules();
        $testData = ['roles' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['roles'][0]['id']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['id'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['id'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['id'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['id'] = EventRoleData::CASHIER;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['id'] = EventRoleData::NOSUCHID;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes()); // no check on valid id, invalid ids are new roles
    }

    public function testValidateUserId()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRoleRequest($stubController))->rules();
        $testData = ['roles' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['roles'][0]['userId']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['userId'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['userId'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['userId'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['userId'] = UserData::TESTUSER;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['userId'] = UserData::NOSUCHID;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateRole()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRoleRequest($stubController))->rules();
        $testData = ['roles' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['roles'][0]['role']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['role'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['role'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['role'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['roles'][0]['role'] = 'organiser';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['role'] = 'registrar';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['role'] = 'cashier';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['role'] = 'accreditation';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['roles'][0]['role'] = 'somethingelse';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }
}
