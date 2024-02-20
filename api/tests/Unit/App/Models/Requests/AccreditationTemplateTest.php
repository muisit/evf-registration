<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\AccreditationTemplate;
use App\Models\WPUser;
use App\Http\Controllers\Templates\Save;
use App\Models\Requests\AccreditationTemplate as ModelRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\AccreditationTemplate as TemplateData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class AccreditationTemplateTest extends TestCase
{
    private function modelsEqual(AccreditationTemplate $f1, AccreditationTemplate $f2)
    {
        $this->assertEquals($f1->getKey(), $f2->getKey());
        $this->assertEquals($f1->name, $f2->name);
        $this->assertEquals($f1->content, $f2->content);
        $this->assertEquals($f1->event_id, $f2->event_id);
        $this->assertEquals($f1->is_default, $f2->is_default);
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'template' => $testData
        ]);
    }

    private function createRequest()
    {
        return new ModelRequest(new Save());
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
        $testData = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
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

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
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

    public function testValidateDefault()
    {
        $data = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['isDefault']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['isDefault'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['isDefault'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['isDefault'] = 'R';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['isDefault'] = 1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['isDefault'] = 'N';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateName()
    {
        $data = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['name']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['name'] = implode('', range(0, 103)); // 202 characters wide
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = 1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateContent()
    {
        $data = [
            'id' => TemplateData::COUNTRY,
            'name' => 'aa',
            'eventId' => 2,
            'isDefault' => 'Y',
            'content' => '{"a":1}',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['content']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['content'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['content'] = '{}';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['content'] = json_encode(['a' => 1, 'b' => true, 'c' => [1,2,3,4]]);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['content'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['content'] = 1; // this is actually a valid JSON object
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }
}
