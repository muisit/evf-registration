<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\DeviceUser;
use App\Models\WPUser;
use App\Models\Requests\AccountPreferences as TheRequest;
use App\Http\Controllers\Device\Account\Preferences as TheController;
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

class AccountPreferencesTest extends TestCase
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
            'followers' => ["handout", "checkin", "checkout", "ranking", "result", "register"],
            'following' => ["handout", "checkin", "checkout", "ranking", "result", "register"]
        ];
        $model = $this->baseTest($testData, $user);
        $user = DeviceUser::find(UserData::DEVICEUSER1);

        $this->assertEquals(["handout", "checkin", "checkout", "ranking", "result", "register"], $user->preferences['account']['followers']);
        $this->assertEquals(["handout", "checkin", "checkout", "ranking", "result", "register"], $user->preferences['account']['following']);
        $this->assertEquals($user->preferences, $model->preferences);
    }

    public function testAuthorization()
    {
        // account linking revolves around device users. Other users are never allowed
        $user = DeviceUser::find(UserData::DEVICEUSER1); // linked to MCAT1
        $user3 = DeviceUser::find(UserData::DEVICEUSER3); // unlinked
        $testData = [
            'followers' => ["handout", "checkin", "checkout", "ranking", "result", "register"],
            'following' => ["handout", "checkin", "checkout", "ranking", "result", "register"]
        ];

        $model = $this->baseTest($testData, $user);
        $this->assertEquals(["handout", "checkin", "checkout", "ranking", "result", "register"], $model->preferences['account']['followers']);
        $this->assertEquals(["handout", "checkin", "checkout", "ranking", "result", "register"], $model->preferences['account']['following']);

        $model = $this->baseTest($testData, $user3);
        $this->assertEquals(["handout", "checkin", "checkout", "ranking", "result", "register"], $model->preferences['account']['followers']);
        $this->assertEquals(["handout", "checkin", "checkout", "ranking", "result", "register"], $model->preferences['account']['following']);
    }

    public function testUnauthorized()
    {
        $testData = [
            'followers' => ["handout", "checkin", "checkout", "ranking", "result", "register"],
            'following' => ["handout", "checkin", "checkout", "ranking", "result", "register"]
        ];
        $user = WPUser::find(WPUserData::TESTUSER);
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);

        $user = AccreditationUser::find(AccreditationUserData::ADMIN);
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
    }

    public function testValidateFollowers()
    {
        // $allowedUserSettings = ["unfollow", "handout", "checkin", "checkout", "ranking", "result", "register"];
        $testData = [
            'followers' => ["handout", "checkin", "checkout", "ranking", "result", "register"],
            'following' => ["handout", "checkin", "checkout", "ranking", "result", "register"]
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // nullable
        unset($testData['followers']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // must be an array, but empty string is considered nullable
        $testData['followers'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['followers'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['followers'] = (object)['a' => 1];
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        foreach (Follow::$allowedUserSettings as $setting) {
            $testData['followers'] = [$setting];
            $validator = Validator::make($testData, $rules);
            $this->assertTrue($validator->passes());
        }

        $testData['followers'] = Follow::$allowedUserSettings;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['followers'] = ['notallowed'];
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateFollowing()
    {
        // $allowedUserSettings = ["unfollow", "handout", "checkin", "checkout", "ranking", "result", "register"];
        $testData = [
            'followers' => ["handout", "checkin", "checkout", "ranking", "result", "register"],
            'following' => ["handout", "checkin", "checkout", "ranking", "result", "register"]
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // nullable
        unset($testData['following']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // must be an array, but empty string is considered nullable
        $testData['following'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['following'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['following'] = (object)['a' => 1];
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        foreach (Follow::$allowedUserSettings as $setting) {
            $testData['following'] = [$setting];
            $validator = Validator::make($testData, $rules);
            $this->assertTrue($validator->passes());
        }

        $testData['following'] = Follow::$allowedUserSettings;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['following'] = ['notallowed'];
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }
}
