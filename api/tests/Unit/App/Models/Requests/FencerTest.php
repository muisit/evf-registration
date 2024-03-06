<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Http\Controllers\Fencers\Save;
use App\Models\Requests\Fencer as FencerRequest;
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

class FencerTest extends TestCase
{
    public function fixtures()
    {
        FencerData::create();
        UserData::create();
        RegistrarData::create();
        EventRoleData::create();
    }

    private function modelsEqual(Fencer $f1, Fencer $f2)
    {
        $this->assertEquals($f1->getKey(), $f2->getKey());
        $this->assertEquals($f1->fencer_surname, $f2->fencer_surname);
        $this->assertEquals($f1->fencer_firstname, $f2->fencer_firstname);
        $this->assertEquals($f1->fencer_gender, $f2->fencer_gender);
        $this->assertEquals($f1->fencer_dob, $f2->fencer_dob);
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
        return new FencerRequest(new Save());
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
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertNotEmpty($model);
        $this->assertEquals(FencerData::MCAT1, $model->getKey());
        $this->assertEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertEquals($testData['lastName'], $model->fencer_surname);
        $this->assertEquals($testData['countryId'], $model->fencer_country);
        $this->assertEquals($testData['gender'], $model->fencer_gender);
        $this->assertEmpty($model->fencer_dob);

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
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertNotEmpty($model);
        $this->assertTrue($model->getKey() > 0);
        $this->assertEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertEquals($testData['lastName'], $model->fencer_surname);
        $this->assertEquals($testData['countryId'], $model->fencer_country);
        $this->assertEquals($testData['gender'], $model->fencer_gender);
        $this->assertEmpty($model->fencer_dob);

        $this->modelsEqual($model, Fencer::where('fencer_id', $model->getKey())->first());
    }

    public function testAuthorization()
    {
        $testData = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
    }

    public function testUnauthorized()
    {
        $testData = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
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

    public function testValidateFirstName()
    {
        $data = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['firstName']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['firstName'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['firstName'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['firstName'] = 'aa';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['firstName'] = '1234567890123456789012345678901234567890123456';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['firstName'] = '123456789012345678901234567890123456789012345';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateLastName()
    {
        $data = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['lastName']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['lastName'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['lastName'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['lastName'] = '123456789012345678901234567890123456789012345';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['lastName'] = '1234567890123456789012345678901234567890123456';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['lastName'] = 'aa';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateCountry()
    {
        $data = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['countryId']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['countryId'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['countryId'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['countryId'] = '0';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['countryId'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['countryId'] = Country::ITA;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateGender()
    {
        $data = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
        
        unset($data['gender']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['gender'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['gender'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['gender'] = 'm';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['gender'] = 'WW';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['gender'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['gender'] = 'M';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['gender'] = 'F';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateDob()
    {
        $data = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['dateOfBirth']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['dateOfBirth'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['dateOfBirth'] = '29 january 2023';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['dateOfBirth'] = Carbon::now()->addMinutes(2)->toDateString();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['dateOfBirth'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['dateOfBirth'] = Carbon::now()->subDays(1)->toDateString();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidatePhoto()
    {
        $data = [
            'id' => 0,
            'firstName' => 'aa',
            'lastName' => 'bb',
            'countryId' => Country::GER,
            'gender' => 'M',
        ];
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['photoStatus']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['photoStatus'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['photoStatus'] = 'aa';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['photoStatus'] = null;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

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
