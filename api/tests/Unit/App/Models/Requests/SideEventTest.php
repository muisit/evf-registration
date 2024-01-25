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
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Mockery;

class SideEventTest extends TestCase
{
    public $authorizeCalls = [];

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

        $request = new SideEventRequest($stubController);

        $stub = $this->createMock(Request::class);
        $stub->expects($this->any())->method('user')->willReturn($user);
        $stub->expects($this->any())->method('get')->with('sides')->willReturn($testData);
        $stub->expects($this->any())->method('all')->willReturn(['sides' => $testData]);
        $stub->expects($this->any())->method('only')->willReturn(['sides' => $testData]);
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
        $rules = (new SideEventRequest($stubController))->rules();
        $testData = ['sides' => $this->testData()];
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
        $stubController = $this->createMock(Controller::class);
        $rules = (new SideEventRequest($stubController))->rules();
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
        $stubController = $this->createMock(Controller::class);
        $rules = (new SideEventRequest($stubController))->rules();
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
        $stubController = $this->createMock(Controller::class);
        $rules = (new SideEventRequest($stubController))->rules();
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
        $stubController = $this->createMock(Controller::class);
        $rules = (new SideEventRequest($stubController))->rules();
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
