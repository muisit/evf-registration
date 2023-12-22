<?php

namespace Tests\Unit\App\Models;

use App\Models\Competition;
use App\Models\Country;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\Registration;
use App\Models\Role;
use App\Models\SideEvent;
use Tests\Support\Data\Accreditation as AccreditationData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\SideEvent as SideData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class SideEventTest extends TestCase
{
    public function fixtures()
    {
        SideData::create();
        AccreditationData::create();
    }

    public function testRelations()
    {
        $se = SideEvent::where('id', SideData::MFCAT1)->first();
        $this->assertNotEmpty($se);
        $this->assertInstanceOf(BelongsTo::class, $se->event());
        $this->assertInstanceOf(Event::class, $se->event()->first());
        $this->assertInstanceOf(Event::class, $se->event);

        $this->assertInstanceOf(HasOne::class, $se->competition());
        $this->assertInstanceOf(Competition::class, $se->competition()->first());
        $this->assertInstanceOf(Competition::class, $se->competition);

        $se = SideEvent::where('id', SideData::GALA)->first();
        $this->assertNotEmpty($se);
        $this->assertEmpty($se->competition);
    }

    public function testHasStarted()
    {
        $se = new SideEvent();
        $se->starts = Carbon::now()->addDays(10)->toDateString();
        $this->assertFalse($se->hasStarted());

        $se->starts = Carbon::now()->subDays(10)->toDateString();
        $this->assertTrue($se->hasStarted());

        // test some possible database values (empty versions)
        // these should all return false
        $se->starts = null;
        $this->assertFalse($se->hasStarted());

        $se->starts = '';
        $this->assertFalse($se->hasStarted());
    }

    public function testAccreditationRelation()
    {
        $event = Event::find(EventData::EVENT1);
        $side = SideEvent::find(SideData::MFCAT1);
        $accreditations = $side->selectAccreditations($event);
        $this->assertCount(1, $accreditations);

        $side = SideEvent::find(SideData::MFTEAM);
        $accreditations = $side->selectAccreditations($event);
        $this->assertCount(4, $accreditations);

        $side = SideEvent::find(SideData::GALA);
        $accreditations = $side->selectAccreditations($event);
        $this->assertCount(0, $accreditations);
    }
}
