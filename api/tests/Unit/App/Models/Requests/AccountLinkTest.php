<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\AccreditationUser;
use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\DeviceUser;
use App\Models\WPUser;
use App\Models\Requests\AccountLink as TheRequest;
use App\Http\Controllers\Device\Account\Link as TheController;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\DeviceUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\SideEvent as SideEventData;
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

class AccountLinkTest extends TestCase
{
    private $request;

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
        $fencer = Fencer::find(FencerData::MCAT1);
        $testData = [
            'firstName' => $fencer->fencer_firstname,
            'lastName' => 'hoelahoep',
            'country' => $fencer->country->country_abbr,
            'gender' => $fencer->fencer_gender,
            'dateOfBirth' => $fencer->fencer_dob,
            'photoStatus' => 'R',
        ];
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        $model = $this->baseTest($testData, $user);

        // user is linked to MCAT1, so the model must be that key
        $this->assertEquals(FencerData::MCAT1, $model->getKey());
        $this->assertEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertEquals($testData['lastName'], $model->fencer_surname);
        $this->assertEquals(Country::GER, $model->fencer_country);
        $this->assertEquals($testData['gender'], $model->fencer_gender);
        $this->assertEquals($testData['dateOfBirth'], $model->fencer_dob);
        $this->assertEquals(Fencer::PICTURE_NONE, $model->fencer_picture); // photoStatus is not updated
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        $this->assertEquals(FencerData::MCAT1, $user->fencer_id);
        $this->assertTrue($this->request->forceCreate); // set to true because fencer was already linked

        $this->assertCount(12, Fencer::where('fencer_id', '>', 0)->get());
        $fencer = Fencer::find(FencerData::MCAT1);
        $testData = [
            'firstName' => $fencer->fencer_firstname,
            'lastName' => $fencer->fencer_surname,
            'country' => $fencer->country->country_abbr,
            'gender' => $fencer->fencer_gender,
            'dateOfBirth' => $fencer->fencer_dob
        ];
        $user = DeviceUser::find(UserData::DEVICEUSER3);
        $model = $this->baseTest($testData, $user);

        // user is not linked to any fencer, so we try to find the correct fencer
        $this->assertEquals(FencerData::MCAT1, $model->getKey());
        $this->assertEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertEquals($testData['lastName'], $model->fencer_surname);
        $this->assertEquals(Country::GER, $model->fencer_country);
        $this->assertEquals($testData['gender'], $model->fencer_gender);
        $this->assertEquals($testData['dateOfBirth'], $model->fencer_dob);
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        $this->assertEquals(FencerData::MCAT1, $user->fencer_id);
        $this->assertCount(12, Fencer::where('fencer_id', '>', 0)->get());
        $this->assertTrue($this->request->forceCreate); // set to true because fencer was found
    }

    public function testCreate()
    {
        $this->assertCount(12, Fencer::where('fencer_id', '>', 0)->get());
        $fencer = Fencer::find(FencerData::MCAT1);
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01'
        ];
        $user = DeviceUser::find(UserData::DEVICEUSER3);
        $model = $this->baseTest($testData, $user);

        // user is not linked to any fencer and no fencer found, so it is created
        // however, because of forceCreate, it is not saved
        $this->assertCount(12, Fencer::where('fencer_id', '>', 0)->get());
        $this->assertEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertEquals($testData['lastName'], $model->fencer_surname);
        $this->assertEquals(Country::ITA, $model->fencer_country);
        $this->assertEquals($testData['gender'], $model->fencer_gender);
        $this->assertEquals($testData['dateOfBirth'], $model->fencer_dob);
        $user = DeviceUser::find(UserData::DEVICEUSER3);
        $this->assertEquals($model->getKey(), $user->fencer_id);
        $this->assertFalse($this->request->forceCreate); // set to false because we had to create a new fencer
    }

    public function testCreate2()
    {
        $this->assertCount(12, Fencer::where('fencer_id', '>', 0)->get());
        $fencer = Fencer::find(FencerData::MCAT1);
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'forceCreate' => 'Y'
        ];
        $user = DeviceUser::find(UserData::DEVICEUSER3);
        $model = $this->baseTest($testData, $user);

        // user is not linked to any fencer and no fencer found, so it is created
        $this->assertCount(13, Fencer::where('fencer_id', '>', 0)->get());
        $this->assertEquals($testData['firstName'], $model->fencer_firstname);
        $this->assertEquals($testData['lastName'], $model->fencer_surname);
        $this->assertEquals(Country::ITA, $model->fencer_country);
        $this->assertEquals($testData['gender'], $model->fencer_gender);
        $this->assertEquals($testData['dateOfBirth'], $model->fencer_dob);
        $user = DeviceUser::find(UserData::DEVICEUSER3);
        $this->assertEquals($model->getKey(), $user->fencer_id);
        $this->assertTrue($this->request->forceCreate); // set to true because we explicitely asked so
    }

    public function testAuthorization()
    {
        // account linking revolves around device users. Other users are never allowed
        $user = DeviceUser::find(UserData::DEVICEUSER1); // linked to MCAT1
        $user3 = DeviceUser::find(UserData::DEVICEUSER3); // unlinked
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'forceCreate' => 'Y'
        ];

        $model = $this->baseTest($testData, $user); // already linked and so allowed to update
        $this->assertNotEmpty($model);

        $model = $this->baseTest($testData, $user3);
        $this->assertNotEmpty($model); // new model created, deviceusers can create unless already linked
    }

    public function testUnauthorized()
    {
        $admin = WPUser::find(WPUserData::TESTUSER);
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'forceCreate' => 'Y'
        ];

        // no privileges, but no exceptions either
        $model = $this->baseTest($testData, $admin);
        $this->assertEmpty($model);

        $model = $this->baseTest($testData, $admin2);
        $this->assertEmpty($model);
    }

    public function testValidateFirstname()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['firstName']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['firstName'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['firstName'] = 'a';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['firstName'] = '123456789012345678901234567890123456789012345';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['firstName'] = '1234567890123456789012345678901234567890123456';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['firstName'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateLastname()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['lastName']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['lastName'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['lastName'] = 'a';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['lastName'] = '123456789012345678901234567890123456789012345';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['lastName'] = '1234567890123456789012345678901234567890123456';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['lastName'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateCountry()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['country']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['country'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['country'] = 'a';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['country'] = 'OTH';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['country'] = 'NOSUCH';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['country'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateDateofbirth()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['dateOfBirth']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['dateOfBirth'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['dateOfBirth'] = 'a';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['dateOfBirth'] = '1900-01-01';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['dateOfBirth'] = Carbon::now()->toDateString(); // must be in the past (1 minute)
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['dateOfBirth'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateGender()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['gender']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['gender'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['gender'] = 'M';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['gender'] = 'F';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['gender'] = 'W';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['gender'] = 'm';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['gender'] = 'NOSUCH';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['gender'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidatePhotoStatus()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['photoStatus']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['photoStatus'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['photoStatus'] = 'N';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['photoStatus'] = 'Y';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['photoStatus'] = 'R';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['photoStatus'] = 'A';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['photoStatus'] = 'W';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['photoStatus'] = 'n';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['photoStatus'] = 'NOSUCH';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['photoStatus'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateForcecreate()
    {
        $testData = [
            'firstName' => 'Harry',
            'lastName' => 'HARRILSON',
            'country' => 'ITA',
            'gender' => 'M',
            'dateOfBirth' => '2000-01-01',
            'photoStatus' => 'N',
            'forceCreate' => 'Y'
        ];
        $this->setRequest($testData);
        $rules = (new TheRequest(new TheController()))->rules();
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertEquals('[]', json_encode($validator->errors()));
        $this->assertTrue($validator->passes());

        unset($testData['forceCreate']);
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['forceCreate'] = '';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['forceCreate'] = 'N';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['forceCreate'] = 'Y';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());

        $testData['forceCreate'] = 'n';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['forceCreate'] = '1';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['forceCreate'] = 'o';
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertFalse($validator->passes());

        $testData['forceCreate'] = null;
        $validator = Validator::make(['fencer' => $testData], $rules);
        $this->assertTrue($validator->passes());
    }
}
