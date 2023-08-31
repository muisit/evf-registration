<?php

namespace Tests\Unit\App\Support;

use App\Models\Country;
use App\Models\Registrar;
use Tests\Support\Data\WPUser as WPUserData;
use Tests\Support\Data\Registrar as Data;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class RegistrarTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
    }

    public function testRelations()
    {
        $role = Registrar::where('user_id', WPUserData::TESTUSER2)->first();
        $this->assertNotEmpty($role);
        $this->assertInstanceOf(BelongsTo::class, $role->country());
        $this->assertEmpty($role->country);

        $role = Registrar::where('user_id', WPUserData::TESTUSER3)->first();
        $this->assertNotEmpty($role);
        $this->assertInstanceOf(Country::class, $role->country()->first());
        $this->assertInstanceOf(Country::class, $role->country);
    }
}
