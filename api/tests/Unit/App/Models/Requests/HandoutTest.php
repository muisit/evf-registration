<?php

namespace Tests\Unit\App\Models\Requests;

use App\Events\AccreditationHandoutEvent;
use App\Models\DeviceUser;
use App\Models\Event;
use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\Accreditation;
use App\Models\WPUser;
use App\Models\Requests\Handout as TheRequest;
use App\Http\Controllers\Accreditations\Handout as TheController;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationDocument as DocData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Follow as FollowData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event as EventFacade;

class HandoutTest extends TestCase
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

    private function createCode($code, $suffix)
    {
        $cid = Accreditation::createControlDigit($code);
        return sprintf("%s%01d%s", $code, $cid, $suffix);
    }

    public function testBasic()
    {
        $user = AccreditationUser::find(AccreditationUserData::ACCREDITATION); // happy flow user
        EventFacade::fake();
        $testData = [
            'badge' => $this->createCode('111270578', sprintf("%04d", EventData::EVENT1))
        ];
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::MFCAT1, $model->getKey());
        EventFacade::assertDispatched(AccreditationHandoutEvent::class, 1);
    }

    public function testAuthorization()
    {
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN); // is organisation
        $accred = AccreditationUser::find(AccreditationUserData::ACCREDITATION); // happy flow user
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // volunteer accreditation user

        EventFacade::fake();
        $testData = [
            'badge' => $this->createCode('111270578', sprintf("%04d", EventData::EVENT1))
        ];
        $model = $this->baseTest($testData, $admin);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $accred);
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $mfcat);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        EventFacade::fake();
        $testData = [
            'badge' => $this->createCode('111270578', sprintf("%04d", EventData::EVENT1))
        ];

        // Here we test that we get a database query exception because sysop is allowed to do
        // anything, but the foreign key relation between badge handout update audit record
        // and the accreditation-user-code fails.
        // To fix this, either not test this, or add an additional check to ensure only
        // real accreditation-code users can enact this, or prevent creation of an audit trail
        // for sysops, or automatically link sysop to the first accreditation-user of type admin
        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSER)->first(); // sysop
            $model = $this->baseTest($testData, $user);
        }, QueryException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first(); // organiser
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

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

    public function testValidateEventObject()
    {
        EventFacade::fake();
        $testData = [
            'badge' => $this->createCode('111270578', sprintf("%04d", EventData::EVENT1))
        ];

        $event = Event::where('event_id', EventData::EVENTFUT)->first();
        request()->merge(['eventObject' => $event]);
        request()->merge($testData);
        $this->request = new TheRequest(new TheController());
        $this->assertException(function () {
            $this->request->validate(request());
        }, AuthorizationException::class);
    }

    public function testValidate()
    {
        EventFacade::fake();
        $testData = [
            'badge' => $this->createCode('111270578', sprintf("%05d", EventData::EVENT1))
        ];

        $this->assertException(function () use ($testData) {
            $user = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
            $model = $this->baseTest($testData, $user);
            $this->assertEmpty($model);
        }, ValidationException::class);
    }

    public function testValidateBadge()
    {
        $testData = [
            'badge' => $this->createCode('111270578', sprintf("%04d", EventData::EVENT1))
        ];

        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // must be exactly 14 characters
        $testData['badge'] = $this->createCode('1112705780', sprintf("%04d", EventData::EVENT1));
        $validator = Validator::make($testData, $rules);
        $this->assertEquals(15, strlen($testData['badge']));
        $this->assertFalse($validator->passes());

        $testData['badge'] = $this->createCode('111270578', sprintf("%05d", EventData::EVENT1));
        $validator = Validator::make($testData, $rules);
        $this->assertEquals(15, strlen($testData['badge']));
        $this->assertFalse($validator->passes());

        $testData['badge'] = $this->createCode('11270578', sprintf("%04d", EventData::EVENT1));
        $validator = Validator::make($testData, $rules);
        $this->assertEquals(13, strlen($testData['badge']));
        $this->assertFalse($validator->passes());

        $testData['badge'] = $this->createCode('111270578', sprintf("%03d", EventData::EVENT1));
        $validator = Validator::make($testData, $rules);
        $this->assertEquals(13, strlen($testData['badge']));
        $this->assertFalse($validator->passes());

        // cannot be an integer
        $testData['badge'] = 14; // use 14 to match the rule parameter
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }
}
