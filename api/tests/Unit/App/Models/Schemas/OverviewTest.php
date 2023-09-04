<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Overview as Schema;
use Tests\Unit\TestCase;

class OverviewTest extends TestCase
{
    public function testCreate()
    {
        $schema = new Schema("aaa", [1, 2, 3]);

        $this->assertEquals("aaa", $schema->country);
        $this->assertEquals([1, 2, 3], $schema->counts);
    }
}
