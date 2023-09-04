<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\ReturnStatus as Schema;
use Tests\Unit\TestCase;

class ReturnStatusTest extends TestCase
{
    public function testCreate()
    {
        $schema = new Schema("bla");
        $this->assertEquals("bla", $schema->status);
        $this->assertEmpty($schema->message);

        $schema = new Schema("bladibla", "hocuspocus");
        $this->assertEquals("bladibla", $schema->status);
        $this->assertEquals("hocuspocus", $schema->message);
    }
}
