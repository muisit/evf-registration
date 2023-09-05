<?php

namespace Tests\Unit\App\Models;

use App\Models\Weapon;
use Tests\Unit\TestCase;

class WeaponTest extends TestCase
{
    public function testRelations()
    {
        $weapons = Weapon::all();
        $this->assertCount(6, $weapons);
    }
}
