<?php

namespace Tests\Unit\App\Support;

use App\Models\Event;
use App\Models\EventRole;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\EventRole as Data;
use Tests\Support\Data\WPUser as WPUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class EventRoleTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
    }

    public function testRelations()
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $this->assertNotEmpty($event);
        
        $roles = $event->roles;
        $this->assertCount(3, $roles);

        $roles[2]->event_id = 12;
        $roles[2]->save();
        $this->assertCount(2, $event->roles()->get());
    }

    public function testEnumeration()
    {
        $role = new EventRole();
        $role->event_id = EventData::EVENT1;
        $role->user_id = WPUser::TESTUSER;
        $role->role_type = 'organiser';
        $role->save();

        $role = new EventRole();
        $role->event_id = EventData::EVENT1;
        $role->user_id = WPUser::TESTUSER;
        $role->role_type = 'cashier';
        $role->save();

        $role = new EventRole();
        $role->event_id = EventData::EVENT1;
        $role->user_id = WPUser::TESTUSER;
        $role->role_type = 'registrar';
        $role->save();

        $role = new EventRole();
        $role->event_id = EventData::EVENT1;
        $role->user_id = WPUser::TESTUSER;
        $role->role_type = 'accreditation';
        $role->save();

        $event = Event::where('event_id', EventData::EVENT1)->first();
        $this->assertNotEmpty($event);
        
        $roles = $event->roles;
        $this->assertCount(7, $roles);
    }

    public function testNotInEnumeration()
    {
        $role = new EventRole();
        $role->event_id = EventData::EVENT1;
        $role->user_id = WPUser::TESTUSER;

        foreach (['accreditor', 'registration', 'accred1tation', 'organisation', 'hod', 'sysop'] as $type) {
            try {
                $role->role_type = $type;
                $role->save();
                $this->assertEquals("Expected QueryException", "");
            } catch (\Exception $e) {
                $this->assertInstanceOf(\Illuminate\Database\QueryException::class, $e);
            }
        }
    }
}
