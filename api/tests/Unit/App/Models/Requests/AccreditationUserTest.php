<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Event;
use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\AccreditationDocument;
use App\Models\WPUser;
use App\Models\Requests\AccreditationUser as TheRequest;
use App\Http\Controllers\Codes\SaveUser as TheController;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationDocument as DocData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
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

class AccreditationUserTest extends TestCase
{
    private $request;

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        request()->merge(['eventObject' => $event]);
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
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $this->assertCount(7, AccreditationUser::where('id', '>', 0)->get());
        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT5,
                'type' => 'dt'
            ],
        ];
        $model = $this->baseTest($testData, $admin);
        $this->assertNotEmpty($model);
        // no change, only an update
        $this->assertCount(7, AccreditationUser::where('id', '>', 0)->get());
        $auser = AccreditationUser::find(AccreditationUserData::VOLUNTEER);
        $this->assertNotEmpty($auser);
        $this->assertEquals(AccreditationUserData::VOLUNTEER, $model->getKey());
        $this->assertEquals('dt', $auser->type);
    }

    public function testCreate()
    {
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $this->assertCount(7, AccreditationUser::where('id', '>', 0)->get());

        $testData = [
            'user' => [
                'id' => 0,
                'fencerId' => FencerData::MCAT4,
                'type' => 'checkin'
            ],
        ];
        $model = $this->baseTest($testData, $admin);
        $this->assertCount(8, AccreditationUser::where('id', '>', 0)->get());
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::DIRECTOR, $model->accreditation_id);
        $this->assertEquals('checkin', $model->type);
    }

    public function testDelete()
    {
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $this->assertCount(7, AccreditationUser::where('id', '>', 0)->get());

        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT4, // fencerId does not need to match
                'type' => 'none'
            ],
        ];
        $model = $this->baseTest($testData, $admin);
        $this->assertCount(6, AccreditationUser::where('id', '>', 0)->get());
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::VOLUNTEER, $model->accreditation_id);
        $this->assertEquals('none', $model->type);
    }

    public function testAuthorization()
    {
        // sysop has before-rights
        // organiser has create/update/delete rights
        $sysop = WPUser::where('ID', WPUserData::TESTUSER)->first();
        $organiser = WPUser::where('ID', WPUserData::TESTUSER2)->first();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN); // organiser

        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT5,
                'type' => 'dt'
            ],
        ];

        // these are set so we can update the accreditation-user codes from the registration application
        $model = $this->baseTest($testData, $sysop);
        $this->assertNotEmpty($model);
        $model = $this->baseTest($testData, $organiser);
        $this->assertNotEmpty($model);

        // only the accreditation-user admin has organiser rights
        $model = $this->baseTest($testData, $admin);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        $cashier = WPUser::where('ID', WPUserData::TESTUSER3)->first();
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT5,
                'type' => 'dt'
            ],
        ];

        $this->assertException(function () use ($testData, $cashier) {
            $model = $this->baseTest($testData, $cashier);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $accr) {
            $model = $this->baseTest($testData, $accr);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $checkin) {
            $model = $this->baseTest($testData, $checkin);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $checkout) {
            $model = $this->baseTest($testData, $checkout);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $dt) {
            $model = $this->baseTest($testData, $dt);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $mfcat) {
            $model = $this->baseTest($testData, $mfcat);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $volunteer) {
            $model = $this->baseTest($testData, $volunteer);
            $this->assertEmpty($model);
        }, AuthorizationException::class);


        // empty or non-existing eventobject returns empty model
        request()->merge(['eventObject' => null]);
        request()->merge($testData);
        $this->request = new TheRequest(new TheController());
        $this->setUser($admin);
        $this->assertException(function () {
            $model = $this->request->validate(request());
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        // if the user type is set to 'none' the system checks the delete authorization, but that
        // is currently enabled for the same set of rights, so we cannot really check that
    }

    public function testValidateUserId()
    {
        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT5,
                'type' => 'dt'
            ],
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // not nullable
        unset($testData['user']['id']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // empty string not allowed
        $testData['user']['id'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // larger or equal to 0
        $testData['user']['id'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['user']['id'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['user']['id'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateFencerId()
    {
        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT5,
                'type' => 'dt'
            ],
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // is required
        unset($testData['user']['fencerId']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // not an empty string
        $testData['user']['fencerId'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // or a text string
        $testData['user']['fencerId'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // may be a string value instead of a direct int
        $testData['user']['fencerId'] = strval(FencerData::MCAT5);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        // must be an existing id
        $testData['user']['fencerId'] = FencerData::NOSUCHFENCER;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateType()
    {
        $testData = [
            'user' => [
                'id' => AccreditationUserData::VOLUNTEER,
                'fencerId' => FencerData::MCAT5,
                'type' => 'dt'
            ],
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // is required
        unset($testData['user']['type']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // not an empty string
        $testData['user']['type'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // not an integer
        $testData['user']['type'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // not a string not in list
        $testData['user']['type'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // these are all accepted
        foreach (['none', 'organiser', 'checkin', 'checkout', 'accreditation', 'dt'] as $type) {
            $testData['user']['type'] = $type;
            $validator = Validator::make($testData, $rules);
            $this->assertTrue($validator->passes());
        }
    }

    public function testOnlyCorrectAccreditation()
    {
        // we can only set volunteers and organisation as accreditation user
        // MCAT1 only has an athlete registration, WCAT5 is only a HoD
        // MCAT5 is coach, volunteer and referee and the latter two are okay
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $testData = [
            'user' => [
                'id' => 0,
                'fencerId' => FencerData::MCAT1,
                'type' => 'dt'
            ],
        ];
        $model = $this->baseTest($testData, $admin);
        $this->assertEmpty($model);

        $testData['user']['fencerId'] = FencerData::WCAT5;
        $model = $this->baseTest($testData, $admin);
        $this->assertEmpty($model);

        // MCAT5 has two accreditations that qualify, but the VOLUNTEER accreditation
        // is of type ORG and that has the most roles attached to it
        $testData['user']['fencerId'] = FencerData::MCAT5;
        $model = $this->baseTest($testData, $admin);
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::VOLUNTEER, $model->accreditation_id);
    }

}
