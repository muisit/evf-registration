<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\AccreditationUser;
use App\Models\DeviceUser;
use App\Models\Country;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\WPUser;
use App\Http\Controllers\Events\SaveConfig as TheController;
use App\Models\Requests\EventConfig as TheRequest;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\EventRole as EventRoleData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\ValidationException;

class EventConfigTest extends TestCase
{
    private $request;

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country
        ]);
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

    public function testUpdate()
    {
        $testData = [
            'event' => [
                'id' => EventData::EVENT1,
                'config' => '{"allow_registration_lower_age":true}'
            ]
            ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model); // event object
        $this->assertEquals(Event::class, get_class($model));
        $this->assertTrue(isset(json_decode($model->event_config)->allow_registration_lower_age));
        $this->assertTrue(json_decode($model->event_config)->allow_registration_lower_age);
    }

    public function testAuthorization()
    {
        $sysop = WPUser::where('ID', UserData::TESTUSER)->first();
        $org = WPUser::where('ID', UserData::TESTUSER2)->first();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN); // is organisation

        $testData = [
            'event' => [
                'id' => EventData::EVENT1,
                'config' => '{"allow_registration_lower_age":true}'
            ]
        ];
        $model = $this->baseTest($testData, $sysop);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $org);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $admin);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        $testData = [
            'event' => [
                'id' => EventData::EVENT1,
                'config' => '{"allow_registration_lower_age":true}'
            ]
        ];

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSER3)->first(); // cashier
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first(); // hod
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

    public function testValidateId()
    {
        $testData = [
            'event' => [
                'id' => EventData::EVENT1,
                'config' => '{"allow_registration_lower_age":true}'
            ]
        ];

        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // not nullable
        unset($testData['event']['id']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['event']['id'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['event']['id'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // must exist
        $testData['event']['id'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['event']['id'] = EventData::NOSUCHEVENT;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateConfig()
    {
        $testData = [
            'event' => [
                'id' => EventData::EVENT1,
                'config' => '{"allow_registration_lower_age":true}'
            ]
        ];

        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // may be null
        unset($testData['event']['config']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // int is valid json
        $testData['event']['config'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['event']['config'] = '{}';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['event']['config'] = '[]';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['event']['config'] = '"string"';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['event']['config'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testAcceptOnlyValidConfig()
    {
        $keys = [
            'allow_registration_lower_age',
            'allow_more_teams',
            'no_accreditations',
            'no_accreditations',
            'use_accreditation',
            'use_registration',
            'require_cards',
            'require_documents',
            'allow_incomplete_checkin',
            'allow_hod_checkout',
            'mark_process_start',
            'combine_checkin_checkout'
        ];
        $nokeys = [
            'allow_registration_lower',
            'use_reg',
            'combin_checkout_checkin',
            'require'
        ];

        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        foreach ($keys as $key) {
            $testData = [
                'event' => [
                    'id' => EventData::EVENT1,
                    'config' => '{"' . $key . '":true}'
                ]
            ];
            $model = $this->baseTest($testData, $user);
            $this->assertNotEmpty($model); // event object
            $this->assertEquals(Event::class, get_class($model));
            $data = json_decode($model->event_config);
            $this->assertTrue(is_object($data));
            $data = (array)$data;
            $this->assertTrue(isset($data[$key]));
            $this->assertTrue($data[$key]);

            $this->assertTrue(isset($data['use_registration']));
            $this->assertTrue($data['use_registration']);
            $this->assertTrue(isset($data['use_accreditation']));
            $this->assertTrue($data['use_accreditation']);
        }

        foreach ($keys as $key) {
            $testData = [
                'event' => [
                    'id' => EventData::EVENT1,
                    'config' => '{"' . $key . '":false}'
                ]
            ];
            $model = $this->baseTest($testData, $user);
            $this->assertNotEmpty($model); // event object
            $this->assertEquals(Event::class, get_class($model));
            $data = json_decode($model->event_config);
            $this->assertTrue(is_object($data));
            $data = (array)$data;
            $this->assertTrue(isset($data[$key]));
            $this->assertFalse($data[$key]);

            // All fields should be set, after the initial loop, but either true or false
            foreach ($keys as $k2) {
                $this->assertTrue(isset($data[$k2]));
            }
        }

        foreach ($nokeys as $key) {
            $testData = [
                'event' => [
                    'id' => EventData::EVENT1,
                    'config' => '{"' . $key . '":true}'
                ]
            ];
            $model = $this->baseTest($testData, $user);
            $this->assertNotEmpty($model); // event object
            $this->assertEquals(Event::class, get_class($model));
            $data = json_decode($model->event_config);
            $this->assertTrue(is_object($data));
            $data = (array)$data;
            $this->assertFalse(isset($data[$key]));
        }
    }
}
