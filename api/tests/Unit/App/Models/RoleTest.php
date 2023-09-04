<?php

namespace Tests\Unit\App\Models;

use App\Models\Role;
use App\Models\RoleType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class RoleTest extends TestCase
{
    public function testRelations()
    {
        $roles = Role::all();
        $this->assertCount(21, $roles);

        $this->assertInstanceOf(BelongsTo::class, $roles[0]->type());
        $this->assertInstanceOf(RoleType::class, $roles[0]->type()->first());
        $this->assertInstanceOf(RoleType::class, $roles[0]->type);
    }
}
