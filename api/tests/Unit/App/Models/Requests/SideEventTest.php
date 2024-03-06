<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\SideEvent;
use App\Models\WPUser;
use App\Models\Requests\SideEvent as SideEventRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\SideEvent as SideEventData;
use App\Http\Controllers\Events\SaveSides;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class SideEventTest extends TestCase
{
    private function testData()
    {
        return [[
            'id' => -1,
            'title' => 'blabla',
            'description' => 'bladibla',
            'starts' => '2020-01-01',
            'costs' => 10.1
        ]];
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'sides' => $testData
        ]);
    }

    private function createRequest($testData)
    {
        $this->setRequest($testData);
        return new SideEventRequest(new SaveSides());
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
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($this->testData(), $user);
        $this->assertNotEmpty($model);
    }

    public function testAuthorization()
    {
        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

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
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $this->assertException(function () use ($testData, $user) {
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testValidateId()
    {
        $testData = ['sides' => $this->testData()];
        $rules = (new SideEventRequest(new SaveSides()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['sides'][0]['id']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['id'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['id'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['id'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['id'] = SideEventData::MFCAT2;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['id'] = SideEventData::NOSUCHEVENT;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes()); // no check on valid id, invalid ids are new roles
    }

    public function testValidateTitle()
    {
        $rules = (new SideEventRequest(new SaveSides()))->rules();
        $testData = ['sides' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['sides'][0]['title']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['title'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['title'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['title'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['title'] = str_repeat('a', 255);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['title'] = str_repeat('a', 256);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateDescription()
    {
        $rules = (new SideEventRequest(new SaveSides()))->rules();
        $testData = ['sides' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['sides'][0]['description']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['description'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['description'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['description'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['description'] = str_repeat('a', 1025);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateStarts()
    {
        $rules = (new SideEventRequest(new SaveSides()))->rules();
        $testData = ['sides' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['sides'][0]['starts']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['starts'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['starts'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['starts'] = '2020-01-01';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['starts'] = '3011-12-02';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['starts'] = '01-01-1980';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['starts'] = '2020';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateCosts()
    {
        $rules = (new SideEventRequest(new SaveSides()))->rules();
        $testData = ['sides' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['sides'][0]['costs']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['costs'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['costs'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['costs'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['sides'][0]['costs'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['costs'] = 13992001.112221122;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['sides'][0]['costs'] = '13992001.112221122';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }
}
