<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\WPUser;
use App\Http\Controllers\Events\Save;
use App\Models\Requests\Event as EventRequest;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class EventTest extends TestCase
{
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

        $a1 = json_decode($e1['config'], true);
        $a2 = json_decode($e2['config'], true);
        $a2keys = array_keys($a2);
        $this->assertEquals(count(array_keys($a1)), count($a2keys));
        foreach ($a1 as $key => $value) {
            $this->assertTrue(in_array($key, $a2keys));
            $this->assertEquals($value, $a2[$key]);
        }
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

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'event' => $testData
        ]);
    }

    private function createRequest()
    {
        return new EventRequest(new Save());
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
        $testData = $this->testData();
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
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

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

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

    public function testValidateName()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['name']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['name'] = 'aa';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['name'] = str_repeat('a', 100);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['name'] = str_repeat('a', 101);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateOpens()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['opens']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['opens'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['opens'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['opens'] = 1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['opens'] = '2020-11-02';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['opens'] = '11-02-2022';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateYear()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['year']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['year'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['year'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['year'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['year'] = 2020;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['year'] = '2020';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['year'] = 2090;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['year'] = 2091;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateDuration()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['duration']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['duration'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['duration'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['duration'] = 0;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['duration'] = 1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['duration'] = '1';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['duration'] = 20;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['duration'] = 21;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateEmail()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['email']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['email'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['email'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['email'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['email'] = 'me@example.org';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['email'] = 'me+filter@e.xa.mple.org';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['email'] = 'me+filter@e.xa.mple.or.g';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes()); // should fail on the TLD, but filter apparently does not
    }

    public function testValidateWeb()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['web']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['web'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['web'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['web'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['web'] = 'https://www.example.org';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['web'] = 'ftp://www.uri.nl';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['web'] = 'http://www.veteransfencing.eu:9982/register/me?query=this#anchor';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateLocation()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['location']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['location'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['location'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['location'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['location'] = str_repeat('a', 45);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['location'] = str_repeat('a', 46);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateCountryId()
    {
        $data = $this->testData();
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

        $data['countryId'] = Country::GER;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['countryId'] = '' . Country::GER;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['countryId'] = 2090;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateSymbol()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['symbol']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['symbol'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['symbol'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['symbol'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['symbol'] = str_repeat('a', 10);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['symbol'] = str_repeat('a', 11);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateCurrency()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['currency']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['currency'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['currency'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['currency'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['currency'] = str_repeat('a', 30);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['currency'] = str_repeat('a', 31);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateBank()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['bank']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['bank'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['bank'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['bank'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['bank'] = str_repeat('a', 100);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['bank'] = str_repeat('a', 101);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateAccount()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['account']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['account'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['account'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['account'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['account'] = str_repeat('a', 100);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['account'] = str_repeat('a', 101);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateAddress()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['address']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['address'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['address'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['address'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateIban()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['iban']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['iban'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['iban'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['iban'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['iban'] = str_repeat('a', 40);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['iban'] = str_repeat('a', 41);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateSwift()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['swift']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['swift'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['swift'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['swift'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['swift'] = str_repeat('a', 20);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['swift'] = str_repeat('a', 21);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateReference()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['reference']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reference'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reference'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reference'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reference'] = str_repeat('a', 255);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reference'] = str_repeat('a', 256);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateRegOpen()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['reg_open']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reg_open'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reg_open'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reg_open'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reg_open'] = '2019-01-01';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reg_open'] = '2019';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reg_open'] = '01-01-2019';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateRegClose()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['reg_close']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reg_close'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reg_close'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reg_close'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reg_close'] = '2019-01-01';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['reg_close'] = '2019';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['reg_close'] = '01-01-2019';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateBaseFee()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['baseFee']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['baseFee'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['baseFee'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['baseFee'] = -1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['baseFee'] = 0;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['baseFee'] = 10.1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['baseFee'] = '10.1';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['baseFee'] = 2090002002.213311;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidateCompetitionFee()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['competitionFee']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['competitionFee'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['competitionFee'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['competitionFee'] = -1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['competitionFee'] = 0;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['competitionFee'] = 10.1;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['competitionFee'] = '10.1';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['competitionFee'] = 2090002002.213311;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }

    public function testValidatePayments()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['payments']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['payments'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['payments'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['payments'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['payments'] = 'all';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['payments'] = 'group';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['payments'] = 'individual';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['payments'] = 'both';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());
    }

    public function testValidateConfig()
    {
        $data = $this->testData();
        $request = $this->createRequest();
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        unset($data['config']);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['config'] = '';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['config'] = 'a';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertFalse($validator->passes());

        $data['config'] = 2019;
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes()); // a raw integer is a valid JSON object

        $data['config'] = '{}';
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());

        $data['config'] = json_encode([1,2,3,4, 'a' => 'b', 'c' => [3,2,1]]);
        $this->setRequest($data);
        $validator = $request->createValidator(request());
        $this->assertTrue($validator->passes());
    }
}
