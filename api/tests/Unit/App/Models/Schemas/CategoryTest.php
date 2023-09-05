<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Category as Schema;
use App\Models\Category;
use Tests\Unit\TestCase;

class CategoryTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->id);
        $this->assertEmpty($schema->name);
        $this->assertEmpty($schema->abbr);
        $this->assertEmpty($schema->type);
        $this->assertEmpty($schema->value);
    }

    public function testCreate()
    {
        $data = new Category();
        $data->category_id = 12;
        $data->category_name = 'aaaaa';
        $data->category_abbr = 'fffff';
        $data->category_type = 'ddddd';
        $data->category_value = 2321;
        $schema = new Schema($data);

        $this->assertEquals($data->category_id, $schema->id);
        $this->assertEquals($data->category_name, $schema->name);
        $this->assertEquals($data->category_abbr, $schema->abbr);
        $this->assertEquals($data->category_type, $schema->type);
        $this->assertEquals($data->category_value, $schema->value);
    }
}
