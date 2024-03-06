<?php

namespace Tests\Unit\App\Models\Requests;

use App\Models\Country;
use App\Models\Event;
use App\Models\WPUser;
use App\Models\Requests\Base;
use App\Http\Controllers\Controller;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\WPUser as UserData;
use Illuminate\Http\Request;
use Tests\Unit\TestCase;

class BaseTest extends TestCase
{
    private function setRequest($testData)
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $country = Country::where('country_id', Country::GER)->first();
        request()->merge([
            'eventObject' => $event,
            'countryObject' => $country
        ])->merge($testData);
    }

    private function createRequest()
    {
        return new Base(new Controller());
    }

    private function baseTest($testData, $user)
    {
        $this->setRequest($testData);
        $this->unsetUser();
        $this->session(['wpuser' => $user->getKey()]);
        return $this->createRequest()->validate(request());
    }

    public function testCreate()
    {
        $testData = ['a' => 12];
        $user = WPUser::where('ID', UserData::TESTUSER)->first();
        $model = $this->baseTest($testData, $user);
        $this->assertEmpty($model);
    }
}
