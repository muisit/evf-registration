<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\DeviceUser;
use App\Models\Fencer;
use App\Models\Follow;
use App\Models\Requests\Follow as FollowRequest;
use App\Http\Controllers\Device\Follow as Controller;
use Tests\Support\Data\DeviceUser as UserData;
use Tests\Support\Data\Fencer as FencerData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Mockery;

class FollowTest extends TestCase
{
    private function setRequest($testData)
    {
        request()->merge([
            'follow' => $testData
        ]);
    }

    private function baseTest($testData)
    {
        $this->setRequest($testData);
        $request = new FollowRequest(new Controller());
        return $request->validate(request());
    }

    public function testNewFollower()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => ['handout', 'checkin', 'checkout']
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());

        $this->assertNotEmpty($follower);
        $this->assertEquals(UserData::DEVICEUSER1, $model->device_user_id);
        $this->assertEquals(FencerData::WCAT5, $model->fencer_id);
        $this->assertFalse($model->isBlocked());
        $this->assertTrue($model->triggersOnEvent('handout'));
        $this->assertTrue($model->triggersOnEvent('checkin'));
        $this->assertTrue($model->triggersOnEvent('checkout'));
        $this->assertFalse($model->triggersOnEvent('ranking'));
        $this->assertFalse($model->triggersOnEvent('result'));
        $this->assertFalse($model->triggersOnEvent('register'));
    }

    public function testUpdate()
    {
        $model = new Follow();
        $model->device_user_id = UserData::DEVICEUSER1;
        $model->fencer_id = FencerData::WCAT5;

        $model->isBlocked(true);
        $model->setPreference('register', true);
        $model->setPreference('ranking', true);
        $model->save();

        $this->assertTrue($model->isBlocked());
        $this->assertFalse($model->triggersOnEvent('register'));
        $this->assertFalse($model->triggersOnEvent('register'));

        // update blocked preference to see if it affects triggersOnEvent
        // this setting is not saved!
        $model->isBlocked(false);
        $this->assertTrue($model->triggersOnEvent('register'));
        $this->assertTrue($model->triggersOnEvent('register'));

        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => ['handout', 'checkin', 'checkout']
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());
        $this->assertNotEmpty($follower);
        $this->assertTrue($model->isBlocked());
        $this->assertFalse($model->triggersOnEvent('handout'));
        $this->assertFalse($model->triggersOnEvent('checkin'));
        $this->assertFalse($model->triggersOnEvent('checkout'));
        $this->assertFalse($model->triggersOnEvent('ranking'));
        $this->assertFalse($model->triggersOnEvent('register'));
        $this->assertFalse($model->triggersOnEvent('result'));
        $this->assertFalse($model->triggersOnEvent('unsupportedevent'));

        $model->isBlocked(false);
        $this->assertTrue($model->triggersOnEvent('handout'));
        $this->assertTrue($model->triggersOnEvent('checkin'));
        $this->assertTrue($model->triggersOnEvent('checkout'));
        $this->assertFalse($model->triggersOnEvent('ranking'));
        $this->assertFalse($model->triggersOnEvent('register'));
        $this->assertFalse($model->triggersOnEvent('result'));
        $this->assertFalse($model->triggersOnEvent('unsupportedevent'));
    }

    public function testValidatePreferences()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $request = new FollowRequest(new Controller());
        $data = [
            'fencer' => $wcat5->uuid,
            'preferences' => ['handout', 'checkin', 'checkout']
        ];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['preferences'] = ['blocked', 'handout', 'checkin', 'checkout', 'ranking', 'result', 'register'];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes()); // blocked is not an allowed thing to set

        $data['preferences'] = ['unfollow', 'handout', 'checkin', 'checkout', 'ranking', 'result', 'register'];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['preferences'] = ['anfollow', 'handout', 'checkin', 'checkout', 'ranking', 'result', 'register'];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = ['blocked'];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = "unfollow";
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        // a single string is cast to an array containing that string, and 'unfollow' is a valid preference
        $this->assertTrue($validator->passes());

        $data['preferences'] = "somethingelse";
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = 0;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        // null is cast to [], which results in 0 differences and is accepted as a generic follow request
        $this->assertTrue($validator->passes());

        unset($data['preferences']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        // leaving out preferences is a generic follow request
        $this->assertTrue($validator->passes());
    }

    public function testValidateFencer()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $request = new FollowRequest(new Controller());
        $data = [
            'fencer' => $wcat5->uuid,
            'preferences' => ['handout', 'checkin', 'checkout']
        ];

        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['fencer'] = $wcat5->uuid . 'postfix';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['fencer'] = $wcat5->getKey();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['fencer'] = 0;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['fencer'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        unset($data['fencer']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testUnfollow()
    {
        $model = new Follow();
        $model->device_user_id = UserData::DEVICEUSER1;
        $model->fencer_id = FencerData::WCAT5;
        $model->isBlocked(true);
        $model->setPreference('register', true);
        $model->setPreference('ranking', true);
        $model->save();
        $this->assertTrue($model->isBlocked());
        $this->assertFalse($model->triggersOnEvent('register'));
        $this->assertFalse($model->triggersOnEvent('ranking'));

        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => ['handout', 'checkin', 'checkout']
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());
        $this->assertNotEmpty($follower);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => ['unfollow']
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());
        $this->assertEmpty($follower);
    }
}
