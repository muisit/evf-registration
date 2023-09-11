<?php

namespace Tests\Unit\App\Support;

use App\Models\Country;
use App\Support\Services\AutocompleteService;
use App\Support\Traits\EVFUser;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;

class AutocompleteServiceTest extends TestCase
{
    public function fixtures()
    {
        FencerData::create();
    }

    public function testSearch()
    {
        $service = new AutocompleteService();
        $result = $service->search('D', null);
        $this->assertCount(6, $result);

        $result = $service->search('D', Country::where('country_id', Country::NED)->first());
        $this->assertCount(2, $result);

        $result = $service->search('D', Country::where('country_id', Country::ITA)->first());
        $this->assertCount(0, $result);

        $result = $service->search('X', Country::where('country_id', Country::GER)->first());
        $this->assertCount(0, $result);
    }
}
