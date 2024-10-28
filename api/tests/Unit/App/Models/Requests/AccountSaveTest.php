<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\DeviceUser;
use App\Models\WPUser;
use App\Models\Requests\AccountSave as TheRequest;
use App\Http\Controllers\Device\Account\Save as TheController;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\DeviceUser as UserData;
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

class AccountSaveTest extends TestCase
{
    private $request;

    private function setRequest($testData)
    {
        request()->merge($testData);
    }

    private function createRequest($testData)
    {
        $this->setRequest($testData);
        return new TheRequest(new TheController());
    }

    private function baseTest($testData, $user)
    {
        $this->request = $this->createRequest($testData);
        if (!empty($user)) {
            request()->setUserResolver(function () use ($user) {
                return $user;
            });
            Auth::login($user); // also set the authenticated user
        }
        return $this->request->validate(request());
    }

    public function testUpdate()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);

        $testData = [
            'language' => 'en',
        ];
        $model = $this->baseTest($testData, $user);
        $user = DeviceUser::find(UserData::DEVICEUSER1);

        $this->assertEquals('en', $user->preferences['account']['language']);
        $this->assertEquals($user->preferences, $model->preferences);
    }

    public function testAuthorization()
    {
        // account linking revolves around device users. Other users are never allowed
        $user = DeviceUser::find(UserData::DEVICEUSER1); // linked to MCAT1
        $user3 = DeviceUser::find(UserData::DEVICEUSER3); // unlinked
        $testData = [
            'language' => 'en',
        ];

        $model = $this->baseTest($testData, $user);
        $this->assertEquals('en', $user->preferences['account']['language']);
        $this->assertEquals($user->preferences, $model->preferences);

        $model = $this->baseTest($testData, $user3);
        $this->assertEquals('en', $user->preferences['account']['language']);
        $this->assertEquals($user->preferences, $model->preferences);
    }

    public function testUnauthorized()
    {
        $testData = [
            'language' => 'en',
        ];
        $user = WPUser::find(WPUserData::TESTUSER);
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);

        $user = AccreditationUser::find(AccreditationUserData::ADMIN);
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
    }

    public function testValidateLanguage()
    {
        // $allowedUserSettings = ["unfollow", "handout", "checkin", "checkout", "ranking", "result", "register"];
        $testData = [
            'language' => 'en',
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // nullable
        unset($testData['language']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // empty string allowed
        $testData['language'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['language'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // this causes an error, but an object will never be passed from POST
        //$testData['language'] = (object)['a' => 1];
        //$validator = Validator::make($testData, $rules);
        //$this->assertFalse($validator->passes());

        $testData['language'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['language'] = '01234567890123456789';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // capped at 20
        $testData['language'] = '012345678901234567890';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }
}
