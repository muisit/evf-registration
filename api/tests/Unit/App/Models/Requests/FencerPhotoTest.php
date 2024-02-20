<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Http\Controllers\Fencers\PhotoSave;
use App\Models\Requests\FencerPhoto as FencerRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class FencerPhotoTest extends TestCase
{
    private function modelsEqual(Fencer $f1, Fencer $f2)
    {
        $this->assertEquals($f1->getKey(), $f2->getKey());
        $this->assertEquals($f1->fencer_picture, $f2->fencer_picture);
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'fencer' => $testData
        ]);
    }

    private function createRequest()
    {
        return new FencerRequest(new PhotoSave());
    }

    private function baseTest($testData, $user)
    {
        $this->setRequest($testData);
        $this->unsetUser();
        $this->session(['wpuser' => $user->getKey()]);
        return $this->createRequest()->validate(request());
    }

    public function testUpdate()
    {
        $testData = [
            'id' => FencerData::MCAT1,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::OTH,
            'gender' => 'F',
            'photoStatus' => 'Y'
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertNotEmpty($model);
        $this->assertEquals(FencerData::MCAT1, $model->getKey());
        $this->assertNotEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertNotEquals($testData['lastName'], $model->fencer_surname);
        $this->assertNotEquals($testData['countryId'], $model->fencer_country);
        $this->assertNotEquals($testData['gender'], $model->fencer_gender);
        $this->assertEquals($testData['photoStatus'], $model->fencer_picture);
        $this->modelsEqual($model, Fencer::where('fencer_id', $model->getKey())->first());
    }

    public function testCreate()
    {
        $testData = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
            'photoStatus' => 'Y'
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
    }

    public function testAuthorization()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = [
            'id' => FencerData::MCAT1,
            'photoStatus' => 'Y'
        ];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        $testData = [
            'id' => FencerData::MCAT1,
            'photoStatus' => 'Y'
        ];


        $this->assertException(function () use ($testData) {
            // no privileges
            $user = WPUser::where('ID', UserData::TESTUSER5)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            // cashier
            $user = WPUser::where('ID', UserData::TESTUSER3)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            // accreditation
            $user = WPUser::where('ID', UserData::TESTUSER3)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }

    public function testValidatePhoto()
    {
        $data = [
            'id' => FencerData::MCAT1,
            'photoStatus' => 'Y'
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['photoStatus']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes()); // status is required

        $data['photoStatus'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['photoStatus'] = 'aa';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['photoStatus'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['photoStatus'] = 'N';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['photoStatus'] = 'R';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['photoStatus'] = 'Y';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['photoStatus'] = 'A';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }
}
