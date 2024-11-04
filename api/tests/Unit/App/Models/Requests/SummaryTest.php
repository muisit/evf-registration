<?php

namespace Tests\Unit\App\Models\Requests;

use App\Jobs\SetupSummary;
use App\Models\DeviceUser;
use App\Models\Event;
use App\Models\Country;
use App\Models\AccreditationUser;
use App\Models\Accreditation;
use App\Models\Role;
use App\Models\WPUser;
use App\Models\Requests\Summary as TheRequest;
use App\Http\Controllers\Accreditations\Summary as TheController;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Queue;

class SummaryTest extends TestCase
{
    private $request;

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        request()->merge(['eventObject' => $event]);
        request()->merge($testData);
    }

    private function setUser($user)
    {
        if (!empty($user)) {
            request()->setUserResolver(function () use ($user) {
                return $user;
            });
            Auth::login($user); // also set the authenticated user
        }
    }

    private function createRequest($testData)
    {
        $this->setRequest($testData);
        return new TheRequest(new TheController());
    }

    private function baseTest($testData, $user)
    {
        $this->request = $this->createRequest($testData);
        $this->setUser($user);
        return $this->request->validate(request());
    }

    public function testBasic()
    {
        Queue::fake();
        $user = WPUser::find(UserData::TESTUSER); // happy flow user
        $testData = [
            'summary' => ["typeId" => Country::GER, "type" => "Country"]
        ];
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEquals(Country::class, get_class($model));
        Queue::assertPushed(SetupSummary::class, 1);
    }

    public function testAuthorization()
    {
        $sysop = WPUser::find(UserData::TESTUSER); // sysop
        $sysop2 = WPUser::find(UserData::TESTUSER2); // sysop
        $org = WPUser::find(UserData::TESTUSERORGANISER); // organiser
        $accredit = WPUser::find(UserData::TESTUSER4); // accreditation

        Queue::fake();
        $testData = [
            'summary' => ["typeId" => Country::GER, "type" => "Country"]
        ];
        $model = $this->baseTest($testData, $sysop);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $sysop2);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $org);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $accredit);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        Queue::fake();
        $testData = [
            'summary' => ["typeId" => Country::GER, "type" => "Country"]
        ];

        $this->assertException(function () use ($testData) {
            $user = WPUser::find(UserData::TESTUSER3); // cashier
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::find(UserData::TESTUSERREGISTRAR);
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSER3)->first(); // cashier
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::find(UserData::TESTUSERGENHOD);
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = AccreditationUser::find(AccreditationUserData::CHECKIN);
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = AccreditationUser::find(AccreditationUserData::DT);
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = DeviceUser::find(DeviceUserData::DEVICEUSER1);
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testValidateEventObject()
    {
        Queue::fake();
        $testData = [
            'summary' => ["typeId" => Country::GER, "type" => "Country"]
        ];

        $event = Event::where('event_id', EventData::NOSUCHEVENT)->first();
        request()->merge(['eventObject' => $event]);
        request()->merge($testData);
        $this->request = new TheRequest(new TheController());
        $this->assertException(function () {
            $this->request->validate(request());
        }, AuthorizationException::class);
    }

    public function testValidateType()
    {
        $testData = [
            'summary' => ["typeId" => Country::GER, "type" => "Country"]
        ];

        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // must be one of Country, Event, Role, Template
        $testData['summary']['type'] = 'Event';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['summary']['type'] = 'Role';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['summary']['type'] = 'Template';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['summary']['type'] = 'Other';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // for some reason we allow nullable values, although it is meaningless
        $testData['summary']['type'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['summary']['type']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateModel()
    {
        $request = new TheRequest(new TheController());
        $data = ['summary' => ["typeId" => Country::GER, "type" => "Country"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data = ['summary' => ["typeId" => TemplateData::ATHLETE, "type" => "Template"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data = ['summary' => ["typeId" => SideEventData::MFCAT1, "type" => "Event"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data = ['summary' => ["typeId" => Role::HOD, "type" => "Role"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        // these do not exist
        $data = ['summary' => ["typeId" => SideEventData::NOSUCHEVENT, "type" => "Event"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data = ['summary' => ["typeId" => TemplateData::NOSUCHID, "type" => "Template"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data = ['summary' => ["typeId" => 1000299, "type" => "Role"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data = ['summary' => ["typeId" => 100292, "type" => "Country"]];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }
}
