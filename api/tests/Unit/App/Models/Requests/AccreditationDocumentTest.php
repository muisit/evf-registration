<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Event;
use App\Models\Follow;
use App\Models\AccreditationUser;
use App\Models\AccreditationDocument;
use App\Models\WPUser;
use App\Models\Requests\AccreditationDocument as TheRequest;
use App\Http\Controllers\Accreditations\SaveDocument as TheController;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\AccreditationDocument as DocData;
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

class AccreditationDocumentTest extends TestCase
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
        $user = AccreditationUser::find(AccreditationUserData::CHECKIN);

        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1,
                'badge' => '11127057800000', // must contain the front-end-id of the MFCAT1 accreditation
                'card' => 12, // must not exist
                'document' => 21, // must not exist
                'fencerId' => FencerData::MCAT1
            ],
            'payload' => json_encode(["anything", "at" => "all"]),
            'status' => 'C',
        ];
        $model = $this->baseTest($testData, $user);

        $this->assertNotEmpty($model);
        $this->assertEquals(DocData::MFCAT1, $model->getKey());
    }

    public function testAuthorization()
    {
        // AccreditationDocument create and update policies are only allowed for checkin and checkout
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1
            ],
            'status' => 'P'
        ];

        $model = $this->baseTest($testData, $checkin);
        $this->assertNotEmpty($model);
        $this->assertEquals(DocData::MFCAT1, $model->getKey());

        $model = $this->baseTest($testData, $checkout);
        $this->assertNotEmpty($model);
        $this->assertEquals(DocData::MFCAT1, $model->getKey());

        // creation is only allowed for checkin
        $testData = [
            'doc' => [
                'id' => 0,
                'badge' => '11127057800000', // must contain the front-end-id of the MFCAT1 accreditation
                'card' => 14, // must not exist
                'document' => 22, // must not exist
                'fencerId' => FencerData::MCAT1
            ],
            'payload' => json_encode(["anything", "at" => "all"]),
            'status' => 'C',
        ];
        $model = $this->baseTest($testData, $checkin);
        $this->assertNotEmpty($model);
        $this->assertNotEquals(DocData::MFCAT1, $model->getKey());
    }

    public function testUnauthorized()
    {
        $admin = AccreditationUser::find(AccreditationUserData::ADMIN);
        $accr = AccreditationUser::find(AccreditationUserData::ACCREDITATION);
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $checkout = AccreditationUser::find(AccreditationUserData::CHECKOUT);
        $dt = AccreditationUser::find(AccreditationUserData::DT);
        $mfcat = AccreditationUser::find(AccreditationUserData::MFCAT1); // mfcat is an accreditation user
        $volunteer = AccreditationUser::find(AccreditationUserData::VOLUNTEER); // volunteer is a checkin user

        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1
            ],
            'status' => 'P'
        ];

        $this->assertException(function () use ($testData, $admin) {
            $model = $this->baseTest($testData, $admin);
            $this->assertEmpty($model);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData, $accr) {
            $model = $this->baseTest($testData, $accr);
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

        $testData = [
            'doc' => [
                'id' => 0,
                'badge' => '11127057800000', // must contain the front-end-id of the MFCAT1 accreditation
                'card' => 19, // must not exist
                'document' => 32, // must not exist
                'fencerId' => FencerData::MCAT1
            ],
            'payload' => json_encode(["anything", "at" => "all"]),
            'status' => 'C',
        ];
        $this->assertException(function () use ($testData, $checkout) {
            $model = $this->baseTest($testData, $checkout);
            $this->assertEmpty($model);
        }, AuthorizationException::class);
    }

    public function testValidateDocId()
    {
        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1
            ]
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make($testData, $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        // not nullable
        unset($testData['doc']['id']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // empty string not allowed
        $testData['doc']['id'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        // larger or equal to 0
        $testData['doc']['id'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());

        $testData['doc']['id'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['doc']['id'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        $testData['doc']['id'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateBadge()
    {
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1,
                'badge' => '11127057800000'
            ]
        ];
        $this->setUser($checkin);
        $this->setRequest($testData);
        $form = new TheRequest(new TheController());
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // badge, if available, must be 14 characters
        $testData['doc']['badge'] = '01234567890123';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['badge'] = '0123456789012';
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        $testData['doc']['badge'] = '012345678901234';
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        // must be a string
        $testData['doc']['badge'] = 0;
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        // may be empty
        $testData['doc']['badge'] = '';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // if doc.id does not exist, content is checked as well for a valid accreditation fe_id for this event
        // In that case, the fencerId must be set as well
        $testData['doc']['id'] = 0;
        $testData['doc']['badge'] = '11127057800000';
        $testData['doc']['fencerId'] = FencerData::MCAT1;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::MFCAT1, $model->accreditation_id);

        $testData['doc']['badge'] = '..1270578.....';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::MFCAT1, $model->accreditation_id);

        // no such badge exception
        $testData['doc']['badge'] = '..1270579.....';
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);
    }

    public function testValidateCard()
    {
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1,
                'badge' => '11127057800000',
                'card' => 12
            ]
        ];
        $this->setUser($checkin);
        $this->setRequest($testData);
        $form = new TheRequest(new TheController());
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // card, if available, must be an integer > 0 and < 999
        $testData['doc']['card'] = '';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['card'] = 0;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['card'] = 999;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // int as string is allowed
        $testData['doc']['card'] = '0';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['card'] = -1;
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        $testData['doc']['card'] = 1000;
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        // if doc.id does not exist, content is checked as well for a valid accreditation fe_id for this event
        // In that case, the fencerId must be set as well
        $testData['doc']['id'] = 0;
        $testData['doc']['badge'] = '11127057800000';
        $testData['doc']['fencerId'] = FencerData::MCAT1;
        $testData['doc']['card'] = 1;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::MFCAT1, $model->accreditation_id); // it picks up this accreditation from the badge

        $testData['doc']['card'] = 132;
        $this->setRequest($testData);
        $form->validate(request()); // should create the entry

        // redoing this should trigger a duplicate card issue
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);
    }

    public function testValidateDocument()
    {
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1,
                'badge' => '11127057800000',
                'document' => 12
            ]
        ];
        $this->setUser($checkin);
        $this->setRequest($testData);
        $form = new TheRequest(new TheController());
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // card, if available, must be an integer > 0 and < 999
        $testData['doc']['document'] = '';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['document'] = 0;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['document'] = 9999;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // int as string is allowed
        $testData['doc']['document'] = '0';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        $testData['doc']['document'] = -1;
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        $testData['doc']['document'] = 10000;
        $this->setRequest($testData);
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);

        // if doc.id does not exist, content is checked as well for a valid accreditation fe_id for this event
        // In that case, the fencerId must be set as well
        $testData['doc']['id'] = 0;
        $testData['doc']['badge'] = '11127057800000';
        $testData['doc']['fencerId'] = FencerData::MCAT1;
        $testData['doc']['document'] = 1;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);
        $this->assertEquals(AccreditationData::MFCAT1, $model->accreditation_id); // it picks up this accreditation from the badge

        $testData['doc']['document'] = 132;
        $this->setRequest($testData);
        $form->validate(request()); // should create the entry

        // redoing this should trigger a duplicate card issue
        $this->assertException(function () use ($form) {
            $model = $form->validate(request());
            $this->assertEmpty($model);
        }, ValidationException::class);
    }

    public function testValidatePayload()
    {
        $checkin = AccreditationUser::find(AccreditationUserData::CHECKIN);
        $testData = [
            'doc' => [
                'id' => DocData::MFCAT1,
                'badge' => '11127057800000'
            ],
            'payload' => '{"a":"b"}'
        ];
        $this->setUser($checkin);
        $this->setRequest($testData);
        $form = new TheRequest(new TheController());
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // payload may be null
        unset($testData['payload']);
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // int is a valid json
        $testData['payload'] = 0;
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // string is a valid json
        $testData['payload'] = '"a"';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // empty string is allowed
        $testData['payload'] = '';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);

        // array is not
        $testData['doc']['payload'] = ['a' => 'b'];
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);
        //$this->assertException(function () use ($form) {
        //    $model = $form->validate(request());
        //    $this->assertEmpty($model);
        //}, ValidationException::class);

        // invalid json
        $testData['doc']['payload'] = '{"a":"b","c":[1,2],true,12,[]}';
        $this->setRequest($testData);
        $model = $form->validate(request());
        $this->assertNotEmpty($model);
        //$this->assertException(function () use ($form) {
        //    $model = $form->validate(request());
        //    $this->assertEmpty($model);
        //}, ValidationException::class);
    }
}
