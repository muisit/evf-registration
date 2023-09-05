<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Role as Schema;
use App\Models\Role;
use App\Models\RoleType;
use Tests\Unit\TestCase;

class RoleTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->id);
        $this->assertEmpty($schema->name);
        $this->assertEmpty($schema->type);
    }

    public function testCreate()
    {
        $data = new Role();
        $data->role_id = 12;
        $data->role_name = 'aaaaa';
        $data->role_type = RoleType::COUNTRY;
        $schema = new Schema($data);

        $this->assertEquals($data->role_id, $schema->id);
        $this->assertEquals($data->role_name, $schema->name);
        $this->assertEquals('Country', $schema->type);
    }
}
