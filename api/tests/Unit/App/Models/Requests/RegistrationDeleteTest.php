<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\WPUser;
use App\Http\Controllers\Registrations\Delete;
use App\Models\Requests\RegistrationDelete;
use App\Support\Enums\PaymentOptions;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Support\Data\EventRole as EventRoleData;
use Tests\Support\Data\Registration as RegistrationData;
use Tests\Support\Data\SideEvent as SideEventData;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Tests\Unit\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class RegistrationDeleteTest extends TestCase
{
    private function saveRegistration()
    {
        $reg = new Registration();
        $reg->registration_mainevent = EventData::EVENT1;
        $reg->registration_country = Country::GER;
        $reg->registration_fencer = FencerData::MCAT1;
        $reg->registration_event = SideEventData::MFCAT1;
        $reg->registration_role = null;
        $reg->registration_payment = 'G';
        $reg->registration_team = null;
        $reg->save();
        return $reg;
    }

    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country,
            'registration' => $testData
        ]);
    }

    private function baseTest($testData, $user)
    {
        $this->setRequest($testData);
        $this->unsetUser();
        $this->session(['wpuser' => $user->getKey()]);
        $request = new RegistrationDelete(new Delete());
        return $request->validate(request());
    }

    public function testDelete()
    {
        $testData = $this->saveRegistration();
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest(['id' => $testData->registration_id], $user);

        $this->assertNotEmpty($model);
        $this->assertEmpty(Registration::find($model->getKey()));
    }

    public function testAuthorization()
    {
        request()->merge([
            'eventObject' => Event::where('event_id', EventData::EVENT1)->first(),
            'countryObject' => Country::where('country_id', Country::GER)->first()
        ]);
        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];

        $user = WPUser::where('ID', UserData::TESTUSERORGANISER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEmpty(Registration::find($model->getKey()));

        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $user = WPUser::where('ID', UserData::TESTUSERREGISTRAR)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEmpty(Registration::find($model->getKey()));

        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $user = WPUser::where('ID', UserData::TESTUSERHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEmpty(Registration::find($model->getKey()));

        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $user = WPUser::where('ID', UserData::TESTUSERGENHOD)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertNotEmpty($model);
        $this->assertEmpty(Registration::find($model->getKey()));
    }

    public function testUnauthorized()
    {
        $testData = $this->saveRegistration();
        $testData = ['id' => $testData->registration_id];
        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSER3)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSER4)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);

        $this->assertException(function () use ($testData) {
            $user = WPUser::where('ID', UserData::TESTUSER5)->first();
            $model = $this->baseTest($testData, $user);
        }, AuthorizationException::class);
    }
}
