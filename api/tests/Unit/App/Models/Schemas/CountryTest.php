<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Country as Schema;
use App\Models\Country;
use Tests\Unit\TestCase;

class CountryTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->id);
        $this->assertEmpty($schema->name);
        $this->assertEmpty($schema->abbr);
        $this->assertEmpty($schema->path);
    }

    public function testCreate()
    {
        $data = new Country();
        $data->country_id = 12;
        $data->country_name = 'aaaaa';
        $data->country_abbr = 'fffff';
        $schema = new Schema($data);

        $this->assertEquals($data->country_id, $schema->id);
        $this->assertEquals($data->country_name, $schema->name);
        $this->assertEquals($data->country_abbr, $schema->abbr);
        $this->assertEmpty($schema->path);

        $data->country_flag_path = 'dasdadsd';
        $schema = new Schema($data);
        $this->assertEquals($data->country_flag_path, $schema->path);
    }
}
