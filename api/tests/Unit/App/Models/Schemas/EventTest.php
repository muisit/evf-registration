<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Event as Schema;
use App\Models\Schemas\EventType as EventTypeSchema;
use App\Models\Schemas\Country as CountrySchema;
use App\Models\Schemas\Bank as BankSchema;
use App\Models\Schemas\SideEvent as SideEventSchema;
use App\Models\Schemas\Competition as CompetitionSchema;
use App\Models\Event;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\SideEvent as SideEventData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Unit\TestCase;

class EventTest extends TestCase
{
    public function fixtures()
    {
        EventData::create();
        SideEventData::create();
        CompetitionData::create();
    }

    public function testEmpty()
    {
        $schema = new Schema();
        $this->assertEmpty($schema->id);
        $this->assertEmpty($schema->name);
        $this->assertEmpty($schema->opens);
        $this->assertEmpty($schema->reg_open);
        $this->assertEmpty($schema->reg_close);
        $this->assertEmpty($schema->year);
        $this->assertEmpty($schema->duration);
        $this->assertEmpty($schema->email);
        $this->assertEmpty($schema->web);
        $this->assertEmpty($schema->location);
        $this->assertEmpty($schema->country);
        $this->assertEmpty($schema->type);
        $this->assertEmpty($schema->bank);
        $this->assertEmpty($schema->payments);
        $this->assertEmpty($schema->feed);
        $this->assertEmpty($schema->config);
        $this->assertEmpty($schema->sideEvents);
        $this->assertEmpty($schema->competitions);
    }

    public function testCreate()
    {
        $event = Event::where('event_id', EventData::EVENT1)->first();
        $schema = new Schema($event);

        $this->assertEquals($event->event_id, $schema->id);
        $this->assertEquals($event->event_name, $schema->name);
        $this->assertEquals($event->event_open, $schema->opens);
        $this->assertEquals($event->event_registration_open, $schema->reg_open);
        $this->assertEquals($event->event_registration_close, $schema->reg_close);
        $this->assertEquals($event->event_year, $schema->year);
        $this->assertEquals($event->event_duration, $schema->duration);
        $this->assertEquals($event->event_email, $schema->email);
        $this->assertEquals($event->event_web, $schema->web);
        $this->assertEquals($event->event_location, $schema->location);
        $this->assertEquals($event->event_payments, $schema->payments);
        $this->assertEquals($event->event_feed, $schema->feed);
        $this->assertEquals($event->event_config, $schema->config);

        $this->assertInstanceOf(EventTypeSchema::class, $schema->type);
        $this->assertInstanceOf(BankSchema::class, $schema->bank);
        $this->assertInstanceOf(CountrySchema::class, $schema->country);

        $this->assertCount(6, $schema->sideEvents);
        $this->assertInstanceOf(SideEventSchema::class, $schema->sideEvents[0]);
        $this->assertCount(4, $schema->competitions);
        $this->assertInstanceOf(CompetitionSchema::class, $schema->competitions[0]);
    }
}
