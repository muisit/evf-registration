<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Models\Requests\FencerPhoto as FencerRequest;
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

class FencerPhotoTest extends TestCase
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
            'countryId' => Country::OTH,
            'gender' => 'F',
            'photoStatus' => 'Y'
        ];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
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

        $this->assertCount(0, $this->authorizeCalls);
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
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
    }

    public function testUnauthorized()
    {
        $testData = [
            'id' => FencerData::MCAT1,
            'photoStatus' => 'Y'
        ];

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
    }

    public function testValidatePhoto()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new FencerRequest($stubController))->rules();

        $testData = [
            'fencer' => [
                'id' => FencerData::MCAT1,
                'photoStatus' => 'Y'
            ]
        ];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['fencer']['photoStatus']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes()); // status is required
        $testData['fencer']['photoStatus'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['photoStatus'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['fencer']['photoStatus'] = null;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
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
