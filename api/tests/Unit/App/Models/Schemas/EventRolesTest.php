<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Event;
use App\Models\EventRole as Model;
use App\Models\Schemas\EventRoles as Schema;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;

class EventRolesTest extends TestCase
{
    public function testCreate()
    {
        $event = Event::find(EventData::EVENT1);
        $schema = new Schema($event);
        $this->assertEquals(5, count($schema->roles));
        $this->assertEquals(10, count($schema->users));
    }
}
