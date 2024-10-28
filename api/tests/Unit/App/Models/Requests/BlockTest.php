<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\DeviceUser;
use App\Models\Event;
use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\AccreditationDocument;
use App\Models\WPUser;
use App\Models\Requests\Block as TheRequest;
use App\Http\Controllers\Device\Block as TheController;
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

class BlockTest extends TestCase
{
    private $request;

    private function setRequest($testData)
    {
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
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        // deviceuser2 follows mcat1, which is deviceuser1
        $testData = [
            'block' => [
                'id' => $user2->uuid,
                'block' => 'Y'
            ],
        ];
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEquals(FollowData::DEVICEFOLLOWER2, $model->getKey());
        $this->assertNotEmpty($model->preferences['block']);
        $this->assertTrue($model->preferences['block']);

        $testData = [
            'block' => [
                'id' => $user2->uuid,
                'block' => 'N'
            ],
        ];
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEquals(FollowData::DEVICEFOLLOWER2, $model->getKey());
        // block is unset
        $this->assertFalse(isset($model->preferences['block']));
    }

    public function testAuthorization()
    {
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        // deviceuser2 follows mcat1, which is deviceuser1
        // deviceuser2 has uuid this-is-also-a-uuid
        $testData = [
            'block' => [
                'id' => $user2->uuid,
                'block' => 'Y'
            ],
        ];
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        $user = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        // user1 cannot block himself, because he does not follow himself
        $testData = [
            'block' => [
                'id' => $user->uuid,
                'block' => 'Y'
            ],
        ];
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);

        // the policy further checks that the fencer_id of the deviceuser matches
        // the fencer_id of the follow-model, but the way the request creates
        // the model requires that relation always anyway. So we cannot check the
        // policy here
    }

    public function testValidateBlockId()
    {
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $testData = [
            'block' => [
                'id' => $user2->uuid,
                'block' => 'Y'
            ],
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // not nullable
        unset($testData['block']['id']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // empty string not allowed
        $testData['block']['id'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // must be an existing uuid
        $testData['block']['id'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['block']['id'] = $user3->uuid;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateBlock()
    {
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $testData = [
            'block' => [
                'id' => $user2->uuid,
                'block' => 'Y'
            ],
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // is required
        unset($testData['block']['block']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // not an empty string
        $testData['block']['block'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // or a random text string
        $testData['block']['block'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['block']['block'] = 'Y';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['block']['block'] = 'N';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }
}
