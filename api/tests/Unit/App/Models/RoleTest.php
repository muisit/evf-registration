<?php

namespace Tests\Unit\App\Models;

use App\Models\Role;
use App\Models\RoleType;
use App\Models\Event;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Event as EventData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class RoleTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
        AccreditationData::create();
    }

    public function testRelations()
    {
        $roles = Role::all();
        $this->assertCount(21, $roles);

        $this->assertInstanceOf(BelongsTo::class, $roles[0]->type());
        $this->assertInstanceOf(RoleType::class, $roles[0]->type()->first());
        $this->assertInstanceOf(RoleType::class, $roles[0]->type);
    }

    public function testAccreditationRelation()
    {
        $event = Event::find(EventData::EVENT1);
        $role = Role::find(Role::REFEREE);
        $accreditations = $role->selectAccreditations($event);
        $this->assertCount(1, $accreditations);

        $role = Role::find(Role::MEDICAL);
        $accreditations = $role->selectAccreditations($event);
        $this->assertCount(0, $accreditations);

        $role = Role::find(Role::COACH);
        $accreditations = $role->selectAccreditations($event);
        $this->assertCount(1, $accreditations);
    }
}
