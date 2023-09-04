<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Schemas\EventType as Schema;
use Tests\Unit\TestCase;

class EventTypeTest extends TestCase
{
    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->name);
    }

    public function testCreate()
    {
        $event = new Event();
        $event->event_type = EventType::INDIVIDUAL;
        $schema = new Schema($event);
        $this->assertEquals("European Individual", $schema->name);
    }
}
