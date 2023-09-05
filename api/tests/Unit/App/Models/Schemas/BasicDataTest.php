<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\BasicData as Schema;
use App\Models\Category;
use App\Models\Weapon;
use App\Models\Country;
use App\Models\Role;
use App\Models\RoleType;
use Tests\Unit\TestCase;

class BasicDataTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->categories);
        $this->assertEmpty($schema->weapons);
        $this->assertEmpty($schema->countries);
        $this->assertEmpty($schema->roles);
    }

    public function testCreateCategory()
    {
        $data = new Category();
        $data->category_id = 12;
        $data->category_name = 'aaaaa';
        $data->category_abbr = 'fffff';
        $data->category_type = 'ddddd';
        $data->category_value = 2321;
        $schema = new Schema();
        $schema->add(collect([$data]));

        $this->assertCount(1, $schema->categories);
        $this->assertEmpty($schema->weapons);
        $this->assertEmpty($schema->countries);
        $this->assertEmpty($schema->roles);
    }

    public function testCreateRole()
    {
        $data = new Role();
        $data->role_id = 12;
        $data->role_name = 'aaaaa';
        $data->role_type = RoleType::COUNTRY;
        $schema = new Schema();
        $schema->add(collect([$data]));

        $this->assertCount(1, $schema->roles);
        $this->assertEmpty($schema->weapons);
        $this->assertEmpty($schema->countries);
        $this->assertEmpty($schema->categories);
    }

    public function testCreateWeapon()
    {
        $data = new Weapon();
        $data->weapon_id = 12;
        $data->weapon_name = 'aaaaa';
        $data->weapon_abbr = 'fffff';
        $data->weapon_gender = 'rrrr';
        $schema = new Schema();
        $schema->add(collect([$data]));

        $this->assertCount(1, $schema->weapons);
        $this->assertEmpty($schema->categories);
        $this->assertEmpty($schema->countries);
        $this->assertEmpty($schema->roles);
    }

    public function testCreateCountry()
    {
        $data = new Country();
        $data->country_id = 12;
        $data->country_name = 'aaaaa';
        $data->country_abbr = 'fffff';
        $schema = new Schema();
        $schema->add(collect([$data]));

        $this->assertCount(1, $schema->countries);
        $this->assertEmpty($schema->weapons);
        $this->assertEmpty($schema->categories);
        $this->assertEmpty($schema->roles);
    }
}
