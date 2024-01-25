<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Models\Requests\Event as EventRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Mockery;

class EventTest extends TestCase
{
    public $authorizeCalls = [];

    private function dataEquals($e1, $e2)
    {
        $this->assertEquals($e1['id'], $e2['id']);
        $this->assertEquals($e1['name'], $e2['name']);
        $this->assertEquals($e1['opens'], $e2['opens']);
        $this->assertEquals($e1['year'], $e2['year']);
        $this->assertEquals($e1['duration'], $e2['duration']);
        $this->assertEquals($e1['email'], $e2['email']);
        $this->assertEquals($e1['web'], $e2['web']);
        $this->assertEquals($e1['location'], $e2['location']);
        $this->assertEquals($e1['countryId'], $e2['countryId']);
        $this->assertEquals($e1['symbol'], $e2['symbol']);
        $this->assertEquals($e1['currency'], $e2['currency']);
        $this->assertEquals($e1['bank'], $e2['bank']);
        $this->assertEquals($e1['account'], $e2['account']);
        $this->assertEquals($e1['address'], $e2['address']);
        $this->assertEquals($e1['iban'], $e2['iban']);
        $this->assertEquals($e1['swift'], $e2['swift']);
        $this->assertEquals($e1['reference'], $e2['reference']);
        $this->assertEquals($e1['reg_open'], $e2['reg_open']);
        $this->assertEquals($e1['reg_close'], $e2['reg_close']);
        $this->assertEquals($e1['baseFee'], $e2['baseFee']);
        $this->assertEquals($e1['competitionFee'], $e2['competitionFee']);
        $this->assertEquals($e1['payments'], $e2['payments']);
        $this->assertEquals($e1['config'], $e2['config']);
    }

    private function modelToData(Event $e)
    {
        return [
            'id' => $e->getKey(),
            'name' => $e->event_name,
            'opens' => $e->event_open,
            'year' => $e->event_year,
            'duration' => $e->event_duration,
            'email' => $e->event_email,
            'web' => $e->event_web,
            'location' => $e->event_location,
            'countryId' => $e->event_country,
            'symbol' => $e->event_currency_symbol,
            'currency' => $e->event_currency_name,
            'bank' => $e->event_bank,
            'account' => $e->event_account_name,
            'address' => $e->event_organisers_address,
            'iban' => $e->event_iban,
            'swift' => $e->event_swift,
            'reference' => $e->event_reference,
            'reg_open' => $e->event_registration_open,
            'reg_close' => $e->event_registration_close,
            'baseFee' => $e->event_base_fee,
            'competitionFee' => $e->event_competition_fee,
            'payments' => $e->event_payments,
            'config' => $e->event_config
        ];
    }

    private function modelsEqual(Event $e1, Event $e2)
    {
        return $this->dataEquals($this->modelToData($e1), $this->modelToData($e2));
    }

    private function testData()
    {
        return [
            'id' => EventData::EVENT1,
            'name' => 'testname',
            'opens' => '2023-01-02',
            'year' => '2023',
            'duration' => 5,
            'email' => 'mail@example.org',
            'web' => 'https://example.org',
            'location' => 'Example Stadium',
            'countryId' => Country::GER,
            'currency' => 'USD',
            'symbol' => '$',
            'bank' => 'Example Bank',
            'account' => 'Account Holder',
            'address' => 'Organisers Address',
            'iban' => 'Random IBAN string',
            'swift' => 'SWIFT/BIC',
            'reference' => 'In Reference To',
            'reg_open' => '2023-01-01',
            'reg_close' => '2023-02-01',
            'baseFee' => '10',
            'competitionFee' => '20',
            'payments' => 'all',
            'config' => '{"allow_registration_lower_age":false,"allow_more_teams":false,"no_accreditations":true,"use_accreditation":false,"use_registration":true}'
        ];
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
                $this->callback(fn($arg) => empty($arg) || $arg == Event::class || get_class($arg) == Event::class)
            )
            ->willReturn(true);

        $request = new EventRequest($stubController);

        $stub = $this->createMock(Request::class);
        $stub->expects($this->any())->method('user')->willReturn($user);
        $stub->expects($this->once())->method('get')->with('event')->willReturn($testData);
        $stub->expects($this->any())->method('all')->willReturn(['event' => $testData]);
        $stub->expects($this->any())->method('only')->willReturn(['event' => $testData]);
        return $request->validate($stub);
    }

    public function testUpdate()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);

        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);

        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
        $this->assertNotEmpty($model);
        $this->dataEquals($testData, $this->modelToData($model));
        $this->modelsEqual($model, Event::where('event_id', $model->getKey())->first());
    }

    public function testAuthorization()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // no privileges
        $user = WPUser::where('ID', UserData::TESTUSER5)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // cashier
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);

        // accreditation
        $user = WPUser::where('ID', UserData::TESTUSER3)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertCount(1, $this->authorizeCalls);
        $this->assertEquals('update', $this->authorizeCalls[0]);
    }

    public function testValidateName()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['name']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['name'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['name'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['name'] = 'aa';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['name'] = str_repeat('a', 100);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['name'] = str_repeat('a', 101);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateOpens()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['opens']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['opens'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['opens'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['opens'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['opens'] = '2020-11-02';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['opens'] = '11-02-2022';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateYear()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['year']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['year'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['year'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['year'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['year'] = 2020;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['year'] = '2020';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['year'] = 2090;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['year'] = 2091;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateDuration()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['duration']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['duration'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['duration'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['duration'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['duration'] = 1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['duration'] = '1';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['duration'] = 20;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['duration'] = 21;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateEmail()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['email']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['email'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['email'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['email'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['email'] = 'me@example.org';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['email'] = 'me+filter@e.xa.mple.org';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['email'] = 'me+filter@e.xa.mple.or.g';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes()); // should fail on the TLD, but filter apparently does not
    }

    public function testValidateWeb()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['web']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['web'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['web'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['web'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['web'] = 'https://www.example.org';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['web'] = 'ftp://www.uri.nl';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['web'] = 'http://www.veteransfencing.eu:9982/register/me?query=this#anchor';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateLocation()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['location']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['location'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['location'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['location'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['location'] = str_repeat('a', 45);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['location'] = str_repeat('a', 46);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateCountryId()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['countryId']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['countryId'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['countryId'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['countryId'] = Country::GER;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['countryId'] = '' . Country::GER;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['countryId'] = 2090;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateSymbol()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['symbol']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['symbol'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['symbol'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['symbol'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['symbol'] = str_repeat('a', 10);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['symbol'] = str_repeat('a', 11);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateCurrency()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['currency']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['currency'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['currency'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['currency'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['currency'] = str_repeat('a', 30);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['currency'] = str_repeat('a', 31);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateBank()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['bank']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['bank'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['bank'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['bank'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['bank'] = str_repeat('a', 100);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['bank'] = str_repeat('a', 101);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateAccount()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['account']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['account'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['account'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['account'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['account'] = str_repeat('a', 100);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['account'] = str_repeat('a', 101);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateAddress()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['address']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['address'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['address'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['address'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateIban()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['iban']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['iban'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['iban'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['iban'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['iban'] = str_repeat('a', 40);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['iban'] = str_repeat('a', 41);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateSwift()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['swift']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['swift'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['swift'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['swift'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['swift'] = str_repeat('a', 20);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['swift'] = str_repeat('a', 21);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateReference()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['reference']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reference'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reference'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reference'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reference'] = str_repeat('a', 255);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reference'] = str_repeat('a', 256);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateRegOpen()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['reg_open']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reg_open'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reg_open'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reg_open'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reg_open'] = '2019-01-01';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reg_open'] = '2019';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reg_open'] = '01-01-2019';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateRegClose()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['reg_close']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reg_close'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reg_close'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reg_close'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reg_close'] = '2019-01-01';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['reg_close'] = '2019';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['reg_close'] = '01-01-2019';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateBaseFee()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['baseFee']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['baseFee'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['baseFee'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['baseFee'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['baseFee'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['baseFee'] = 10.1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['baseFee'] = '10.1';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['baseFee'] = 2090002002.213311;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidateCompetitionFee()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['competitionFee']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['competitionFee'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['competitionFee'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['competitionFee'] = -1;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['competitionFee'] = 0;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['competitionFee'] = 10.1;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['competitionFee'] = '10.1';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['competitionFee'] = 2090002002.213311;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }

    public function testValidatePayments()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['payments']);
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['payments'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['payments'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['payments'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['payments'] = 'all';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['payments'] = 'group';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['payments'] = 'individual';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['payments'] = 'both';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
    }

    public function testValidateConfig()
    {
        $stubController = $this->createMock(Controller::class);
        $rules = (new EventRequest($stubController))->rules();
        $testData = ['event' => $this->testData()];
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());

        unset($testData['event']['config']);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['config'] = '';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['config'] = 'a';
        $validator = Validator::make($testData, $rules);
        $this->assertFalse($validator->passes());
        $testData['event']['config'] = 2019;
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes()); // a raw integer is a valid JSON object
        $testData['event']['config'] = '{}';
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
        $testData['event']['config'] = json_encode([1,2,3,4, 'a' => 'b', 'c' => [3,2,1]]);
        $validator = Validator::make($testData, $rules);
        $this->assertTrue($validator->passes());
    }
}
