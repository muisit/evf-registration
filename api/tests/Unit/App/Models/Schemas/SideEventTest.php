<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\SideEvent as Schema;
use App\Models\SideEvent;
use Tests\Unit\TestCase;

class SideEventTest extends TestCase
{
    public function testCreate()
    {
        $data = new SideEvent();
        $data->id = 12;
        $data->event_id = 36;
        $data->title = "blabla";
        $data->description = "hocuspocus";
        $data->costs = 1200.1234;
        $data->starts = "aaaaa";
        $schema = new Schema($data);

        $this->assertEquals($data->id, $schema->id);
        $this->assertEquals($data->title, $schema->title);
        $this->assertEquals($data->description, $schema->description);
        $this->assertEquals($data->starts, $schema->starts);
        $this->assertEquals($data->costs, $schema->costs);
        $this->assertEmpty($schema->competition);

        $data->competition_id = 3312;
        $schema = new Schema($data);
        $this->assertEquals($data->competition_id, $schema->competition);
    }
}
