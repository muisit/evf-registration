<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Models\Requests\Fencer as FencerRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Mockery;

class FencerTest extends TestCase
{
    public $authorizeCalls = [];

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

    private function baseTest($testData, $user)
    {
        $this->authorizeCalls = [];
        $stubController = $this->createMock(Controller::class);
        $stubController
            ->method('authorize')
            ->with(
                $this->callback(function ($arg) {
                    $this->authorizeCalls[] = $arg;
                    return true;
                }),
                $this->callback(fn($arg) => empty($arg) || $arg == Fencer::class || get_class($arg) == Fencer::class)
            )
            ->willReturn(true);

        $request = new FencerRequest($stubController);

        $stub = $this->createMock(Request::class);
        $stub->expects($this->any())->method('user')->willReturn($user);
        $stub->expects($this->once())->method('get')->with('fencer')->willReturn($testData);
        $stub->expects($this->any())->method('all')->willReturn(['fencer' => $testData]);
        $stub->expects($this->any())->method('only')->willReturn(['fencer' => $testData]);
        return $request->validate($stub);
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

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
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

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
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
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
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
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('create', $this->authorizeCalls[0]);
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

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('not/ever', $this->authorizeCalls[1]);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('not/ever', $this->authorizeCalls[1]);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(2, $this->authorizeCalls);
        $this->assertEquals('not/ever', $this->authorizeCalls[1]);
    }

    public function testValidateFirstName()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => 0,
                'firstName' => 'aa',
                'lastName' => 'bb',
                'countryId' => Country::GER,
                'gender' => 'M',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['fencer']['firstName']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['firstName'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['firstName'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['firstName'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['firstName'] = '1234567890123456789012345678901234567890123456';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['firstName'] = '123456789012345678901234567890123456789012345';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateLastName()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => 0,
                'firstName' => 'aa',
                'lastName' => 'bb',
                'countryId' => Country::GER,
                'gender' => 'M',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['fencer']['lastName']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['lastName'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['lastName'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['lastName'] = '123456789012345678901234567890123456789012345';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['lastName'] = '1234567890123456789012345678901234567890123456';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['lastName'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateCountry()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => 0,
                'firstName' => 'aa',
                'lastName' => 'bb',
                'countryId' => Country::GER,
                'gender' => 'M',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['fencer']['countryId']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['countryId'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['countryId'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['countryId'] = '0';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['countryId'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['countryId'] = Country::ITA;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateGender()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => 0,
                'firstName' => 'aa',
                'lastName' => 'bb',
                'countryId' => Country::GER,
                'gender' => 'M',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        
        unset($testData['fencer']['gender']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['gender'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['gender'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['gender'] = 'm';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['gender'] = 'WW';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['gender'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['gender'] = 'M';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['gender'] = 'F';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateDob()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => 0,
                'firstName' => 'aa',
                'lastName' => 'bb',
                'countryId' => Country::GER,
                'gender' => 'M',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['fencer']['dateOfBirth']);
        $validator = Validator::make($testData, $rules);        $validator = Validator::make($testData, $rules);

        $this->assertTrue($validator->passes());
        $testData['fencer']['dateOfBirth'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['dateOfBirth'] = '29 january 2023';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['dateOfBirth'] = Carbon::now()->addMinutes(2)->toDateString();
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['dateOfBirth'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['dateOfBirth'] = Carbon::now()->subDays(1)->toDateString();
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidatePhoto()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => 0,
                'firstName' => 'aa',
                'lastName' => 'bb',
                'countryId' => Country::GER,
                'gender' => 'M',
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['fencer']['photoStatus']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['photoStatus'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['photoStatus'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['photoStatus'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['photoStatus'] = 'N';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['photoStatus'] = 'R';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['photoStatus'] = 'Y';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['fencer']['photoStatus'] = 'A';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }
}
