<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Weapon as Schema;
use App\Models\Weapon;
use Tests\Unit\TestCase;

class WeaponTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->id);
        $this->assertEmpty($schema->name);
        $this->assertEmpty($schema->abbr);
        $this->assertEmpty($schema->gender);
    }

    public function testCreate()
    {
        $data = new Weapon();
        $data->weapon_id = 12;
        $data->weapon_name = 'aaaaa';
        $data->weapon_abbr = 'fffff';
        $data->weapon_gender = 'rrrr';
        $schema = new Schema($data);

        $this->assertEquals($data->weapon_id, $schema->id);
        $this->assertEquals($data->weapon_name, $schema->name);
        $this->assertEquals($data->weapon_abbr, $schema->abbr);
        $this->assertEquals($data->weapon_gender, $schema->gender);
    }
}
