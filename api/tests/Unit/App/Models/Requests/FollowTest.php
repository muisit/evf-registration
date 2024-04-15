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
                'preferences' => json_encode(['handout', 'checkin', 'checkout'])
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());

        $this->assertNotEmpty($follower);
        $this->assertEquals(UserData::DEVICEUSER1, $model->device_user_id);
        $this->assertEquals(FencerData::WCAT5, $model->fencer_id);
        $this->assertFalse($model->isBlocked());
        $this->assertTrue($model->feedHandout());
        $this->assertTrue($model->feedCheckin());
        $this->assertTrue($model->feedCheckout());
        $this->assertFalse($model->feedRanking());
        $this->assertFalse($model->feedResult());
        $this->assertFalse($model->feedRegister());
    }

    public function testUpdate()
    {
        $model = new Follow();
        $model->device_user_id = UserData::DEVICEUSER1;
        $model->fencer_id = FencerData::WCAT5;
        $model->isBlocked(true);
        $model->feedRegister(true);
        $model->feedRanking(true);
        $model->save();
        $this->assertTrue($model->isBlocked());
        $this->assertTrue($model->feedRegister());
        $this->assertTrue($model->feedRanking());

        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => json_encode(['handout', 'checkin', 'checkout'])
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());
        $this->assertNotEmpty($follower);
        $this->assertFalse($model->isBlocked());
        $this->assertTrue($model->feedHandout());
        $this->assertTrue($model->feedCheckin());
        $this->assertTrue($model->feedCheckout());
        $this->assertFalse($model->feedRanking());
        $this->assertFalse($model->feedResult());
        $this->assertFalse($model->feedRegister());
    }

    public function testValidatePreferences()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $request = new FollowRequest(new Controller());
        $data = [
            'fencer' => $wcat5->uuid,
            'preferences' => json_encode(['handout', 'checkin', 'checkout'])
        ];
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['preferences'] = json_encode(['blocked', 'handout', 'checkin', 'checkout', 'ranking', 'result', 'register']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['preferences'] = json_encode(['blacked', 'handout', 'checkin', 'checkout', 'ranking', 'result', 'register']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = json_encode(['blacked']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = json_encode("blocked");
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['preferences'] = "blocked";
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
        $this->assertFalse($validator->passes());

        unset($data['preferences']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateFencer()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $request = new FollowRequest(new Controller());
        $data = [
            'fencer' => $wcat5->uuid,
            'preferences' => json_encode(['handout', 'checkin', 'checkout'])
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
        $model->feedRegister(true);
        $model->feedRanking(true);
        $model->save();
        $this->assertTrue($model->isBlocked());
        $this->assertTrue($model->feedRegister());
        $this->assertTrue($model->feedRanking());

        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $wcat5 = Fencer::find(FencerData::WCAT5);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => json_encode(['handout', 'checkin', 'checkout'])
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());
        $this->assertNotEmpty($follower);

        $model = $this->baseTest(
            [
                'fencer' => $wcat5->uuid,
                'preferences' => json_encode(['unfollow'])
            ]
        );
        $this->assertNotEmpty($model);
        $follower = Follow::find($model->getKey());
        $this->assertEmpty($follower);
    }
}
