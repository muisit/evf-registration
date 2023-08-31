<?php

namespace Tests\Unit\App\Support;

use App\Models\RoleType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class RoleTypeTest extends TestCase
{
    public function testRelations()
    {
        $types = RoleType::all();
        $this->assertCount(4, $types);
    }
}
