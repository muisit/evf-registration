<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\WPUser;
use App\Http\Controllers\Events\SaveRoles;
use App\Models\Requests\EventRole as EventRoleRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\EventRole as EventRoleData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class EventRoleTest extends TestCase
{
    private function testData()
    {
        return [[
            'id' => EventRoleData::ORGANISER,
            'userId' => UserData::TESTUSERGENHOD,
            'role' => 'organiser'
        ]];
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'roles' => $testData
        ]);
    }

    private function createRequest()
    {
        return new EventRoleRequest(new SaveRoles());
    }

    private function baseTest($testData, $user)
    {
        $this->setRequest($testData);
        $this->unsetUser();
        $this->session(['wpuser' => $user->getKey()]);
        return $this->createRequest()->validate(request());
    }

    public function testUpdate()
    {
        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model); // event object
        $this->assertEquals(Event::class, get_class($model));
    }

    public function testAuthorization()
    {
        $testData = $this->testData();
        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            // no privileges
            $user = WPUser::where('ID', UserData::TESTUSER5)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            // cashier
            $user = WPUser::where('ID', UserData::TESTUSER3)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            // accreditation
            $user = WPUser::where('ID', UserData::TESTUSER3)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testValidateId()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data[0]['id']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['id'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['id'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['id'] = -1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['id'] = EventRoleData::CASHIER;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['id'] = EventRoleData::NOSUCHID;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes()); // no check on valid id, invalid ids are new roles
    }

    public function testValidateUserId()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data[0]['userId']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['userId'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['userId'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['userId'] = -1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['userId'] = UserData::TESTUSER;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['userId'] = UserData::NOSUCHID;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateRole()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data[0]['role']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['role'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['role'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['role'] = -1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data[0]['role'] = 'organiser';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['role'] = 'registrar';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['role'] = 'cashier';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['role'] = 'accreditation';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data[0]['role'] = 'somethingelse';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }
}
