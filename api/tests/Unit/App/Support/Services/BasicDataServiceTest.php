<?php

namespace Tests\Unit\App\Support;

use App\Models\Country;
use App\Models\Weapon;
use App\Models\Category;
use App\Models\Role;
use App\Models\RoleType;
use App\Support\Services\BasicDataService;
use Tests\Unit\TestCase;

class BasicDataServiceTest extends TestCase
{
    public function testCreate()
    {
        $service = new BasicDataService();
        $schema = $service->create();

        $this->assertCount(7, $schema->categories);
        $this->assertCount(6, $schema->weapons);
        $this->assertCount(49, $schema->countries);
        $this->assertCount(21, $schema->roles);

        // check ordering
        $this->assertEquals(Country::ALB, $schema->countries[0]->id);
        $this->assertEquals(Country::UKR, $schema->countries[48]->id);

        $this->assertEquals(Role::COACH, $schema->roles[0]->id);
        $this->assertEquals(Role::DIRECTOR, $schema->roles[20]->id);

        $this->assertEquals(Category::TEAM, $schema->categories[0]->id);
        $this->assertEquals(Category::CAT5, $schema->categories[6]->id);

        $this->assertEquals(Weapon::ME, $schema->weapons[0]->id);
        $this->assertEquals(Weapon::MF, $schema->weapons[1]->id);
        $this->assertEquals(Weapon::WS, $schema->weapons[5]->id);
    }
}
