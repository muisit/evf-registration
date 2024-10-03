<?php

namespace Tests\Unit\App\Models;

use App\Models\Country;
use App\Models\Event;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class CountryTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
        AccreditationData::create();
    }

    public function testRelations()
    {
        $countries = Country::all();
        $this->assertCount(49, $countries);
    }

    public function testAccreditationRelation()
    {
        $event = Event::find(EventData::EVENT1);
        $country = Country::find(Country::GER);
        $accreditations = $country->selectAccreditations($event);
        $this->assertCount(6, $accreditations);
    }
}
