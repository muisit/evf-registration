<?php

namespace Tests\Unit\App\Models;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class EventTypeTest extends TestCase
{
    public function testRelations()
    {
        $types = EventType::all();
        $this->assertCount(4, $types);
    }
}
