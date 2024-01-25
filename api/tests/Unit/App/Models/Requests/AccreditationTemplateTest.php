<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\AccreditationTemplate;
use App\Models\WPUser;
use App\Models\Requests\AccreditationTemplate as ModelRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Mockery;

class AccreditationTemplateTest extends TestCase
{
    public $authorizeCalls = [];

    private function modelsEqual(AccreditationTemplate $f1, AccreditationTemplate $f2)
    {
        $this->assertEquals($f1->getKey(), $f2->getKey());
        $this->assertEquals($f1->name, $f2->name);
        $this->assertEquals($f1->content, $f2->content);
        $this->assertEquals($f1->event_id, $f2->event_id);
        $this->assertEquals($f1->is_default, $f2->is_default);
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
                $this->callback(fn($arg) => empty($arg) || $arg == AccreditationTemplate::class || get_class($arg) == AccreditationTemplate::class)
            )
            ->willReturn(true);

        $request = new ModelRequest($stubController);

        $stub = $this->createMock(Request::class);
        $stub->expects($this->any())->method('user')->willReturn($user);
        $stub->expects($this->once())->method('get')->with('template')->willReturn($testData);
        $stub->expects($this->any())->method('all')->willReturn(['template' => $testData]);
        $stub->expects($this->any())->method('only')->willReturn(['template' => $testData]);
        return $request->validate($stub);
    }

    public function testUpdate()
    {
        $testData = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
        $this->assertNotEmpty($model);
        $this->assertEquals(TemplateData::COUNTRY, $model->getKey());
        $this->assertEquals($testData['name'], $model->name);
        $this->assertEquals($testData['eventId'], 2); // event_id is not updated
        $this->assertEquals($testData['isDefault'], $model->is_default);
        $this->assertEquals($testData['content'], $model->content);

        $this->modelsEqual($model, AccreditationTemplate::where('id', $model->getKey())->first());
    }

    public function testCreate()
    {
        $testData = [
            'id' => 0,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
        $this->assertNotEmpty($model);
        $this->assertTrue($model->getKey() > 0);
        $this->assertEquals($testData['name'], $model->name);
        $this->assertEquals($testData['eventId'], 2); // event_id is not updated
        $this->assertEquals($testData['isDefault'], $model->is_default);
        $this->assertEquals($testData['content'], $model->content);

        $this->modelsEqual($model, AccreditationTemplate::where('id', $model->getKey())->first());
    }

    public function testAuthorizationUpdate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
    }

    public function testUnauthorizedCreate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = [
            'id' => 0,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertEmpty($model); // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
    }

    public function testUnauthorizedUpdate()
    {
        $testData = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertEmpty($model);  // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertEmpty($model); // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertNotEmpty($model); // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertEmpty($model); // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertEmpty($model); // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        //$this->assertEmpty($model); // auth call in mock always returns true, so model is not null-ed
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
    }

    public function testValidateDefault()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new ModelRequest($stubController))->rules();

        $testData = [
            'template' => [
                'id' => TemplateData::COUNTRY,
                'name' => 'aa',
                'eventId' => 2,
                'isDefault' => 'Y',
                'content' => '{"a":1}',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['template']['isDefault']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['isDefault'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['isDefault'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['isDefault'] = 'R';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['isDefault'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['isDefault'] = 'N';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateName()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new ModelRequest($stubController))->rules();

        $testData = [
            'template' => [
                'id' => TemplateData::COUNTRY,
                'name' => 'aa',
                'eventId' => 2,
                'isDefault' => 'Y',
                'content' => '{"a":1}',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['template']['name']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['name'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['name'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['template']['name'] = implode('', range(0, 103)); // 202 characters wide
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['name'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['name'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateContent()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new ModelRequest($stubController))->rules();

        $testData = [
            'template' => [
                'id' => TemplateData::COUNTRY,
                'name' => 'aa',
                'eventId' => 2,
                'isDefault' => 'Y',
                'content' => '{"a":1}',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['template']['content']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['content'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['content'] = '{}';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['template']['content'] = json_encode(['a' => 1, 'b' => true, 'c' => [1,2,3,4]]);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['template']['content'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['template']['content'] = 1; // this is actually a valid JSON object
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }
}
