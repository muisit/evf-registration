<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\DeviceUser;
use App\Models\Event;
use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\Accreditation;
use App\Models\WPUser;
use App\Models\Requests\Codes as TheRequest;
use App\Http\Controllers\Codes\Validate as TheController;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationDocument as DocData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Follow as FollowData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as WPUserData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class CodesTest extends TestCase
{
    private $request;

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        request()->merge(['eventObject' => $event]);
        request()->merge($testData);
    }

    private function createRequest($testData)
    {
        $this->setRequest($testData);
        return new TheRequest(new TheController());
    }

    private function baseTest($testData)
    {
        $this->request = $this->createRequest($testData);
        return $this->request->validate(request());
    }

    private function createCode($prefix, $type, $suffix)
    {
        $id1 = random_int(101, 999);
        $id2 = random_int(101, 999);
        $values = sprintf("%01d%03d%03d", $type, $id1, $id2);
        $cid = Accreditation::createControlDigit($values);
        return sprintf("%s%s%01d%s", $prefix, $values, $cid, $suffix);
    }

    public function testBasic()
    {
        $testData = [
            'codes' => [
                $this->createCode('11', 6, '0001'),
                $this->createCode('22', 5, '0001'),
                $this->createCode('33', 4, '0001'),
                $this->createCode('44', 3, '0001'),
                $this->createCode('55', 2, '0001'),
                $this->createCode('66', 1, '0001')
            ],
            "action" => "anything"
        ];
        $model = $this->baseTest($testData);
        $this->assertNotEmpty($model);
        $this->assertEquals(EventData::EVENT1, $model->getKey());
        $this->assertCount(6, $this->request->codes);
        $this->assertEquals("anything", $this->request->action);
    }

    public function testValidate()
    {
        $testData = [
            'codes' => [
                $this->createCode('11', 6, '001')
            ],
            "action" => "anything"
        ];

        $this->assertException(function () use ($testData) {
            $model = $this->baseTest($testData);
            $this->assertEmpty($model);
        }, ValidationException::class);
    }

    public function testValidateCode()
    {
        $testData = [
            'codes' => [
                $this->createCode('11', 6, '0001')
            ],
            "action" => "anything"
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // the list-of-fields can be absent
        unset($testData['codes']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // list of fields can be empty
        $testData['codes'] = [];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // must be exactly 14 characters
        $testData['codes'] = [$this->createCode('11', 6, '001')];
        $validator = Validator::make($testData, $rules);
        $this->assertEquals(13, strlen($testData['codes'][0]));
        $this->assertFalse($validator->passes());

        $testData['codes'] = [$this->createCode('11', 6, '00001')];
        $validator = Validator::make($testData, $rules);
        $this->assertEquals(15, strlen($testData['codes'][0]));
        $this->assertFalse($validator->passes());

        // cannot be an integer
        $testData['codes'] = [14]; // use 14 to match the rule parameter
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // this one is probably true because we match on 'codes.*', which assumes the codes
        // parameter is an array. If it is not, the rule is skipped and the validation
        // succeeds
        $testData['codes'] = 14; // use 14 to match the rule parameter
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateAction()
    {
        $testData = [
            'codes' => [
                $this->createCode('11', 6, '0001')
            ],
            "action" => "anything"
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // is required
        unset($testData['action']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // not an empty string
        $testData['action'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['action'] = '01234567890123456789012345678901234567890123456789';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['action'] = '012345678901234567890123456789012345678901234567890';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['action'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['action'] = [];
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }
}
