<?php

namespace Tests\Unit\App\Support;

use App\Models\Country;
use Tests\Unit\TestCase;

class CountryTest extends TestCase
{
    public function testRelations()
    {
        $countries = Country::all();
        $this->assertCount(49, $countries);
    }
}
