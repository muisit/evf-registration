<?php

namespace Tests\Unit\App\Support;

use App\Models\WPUser;
use App\Support\Services\DefaultCountryService;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Registrar as RegistrarData;
use Tests\Unit\TestCase;

class DefaultCountryServiceTest extends TestCase
{
    public function fixtures()
    {
        UserData::create();
        RegistrarData::create();
    }

    public function testCreate()
    {
        $this->assertEmpty(DefaultCountryService::determineCountry(WPUser::where('ID', UserData::TESTUSER)->first()));
        $this->assertEmpty(DefaultCountryService::determineCountry(WPUser::where('ID', UserData::TESTUSER2)->first()));
        $this->assertNotEmpty(DefaultCountryService::determineCountry(WPUser::where('ID', UserData::TESTUSERHOD)->first()));
        $this->assertEmpty(DefaultCountryService::determineCountry(WPUser::where('ID', UserData::TESTUSERGENHOD)->first()));
    }
}
