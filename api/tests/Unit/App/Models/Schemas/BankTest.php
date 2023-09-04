<?php

namespace Tests\Unit\App\Models\Schemas;

use App\Models\Schemas\Bank;
use App\Models\Event;
use Tests\Support\Data\Event as Data;
use Tests\Unit\TestCase;

class BankTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
    }

    public function testEmpty()
    {
        $schema = new Bank();
        $this->assertEmpty($schema->symbol);
        $this->assertEmpty($schema->currency);
        $this->assertEmpty($schema->baseFee);
        $this->assertEmpty($schema->competitionFee);
        $this->assertEmpty($schema->bank);
        $this->assertEmpty($schema->account);
        $this->assertEmpty($schema->address);
        $this->assertEmpty($schema->iban);
        $this->assertEmpty($schema->swift);
        $this->assertEmpty($schema->reference);
    }

    public function testCreate()
    {
        $event = Event::where('event_id', Data::EVENT1)->first();
        $schema = new Bank($event);

        $this->assertEquals($event->event_currency_symbol, $schema->symbol);
        $this->assertEquals($event->event_currency_name, $schema->currency);
        $this->assertEquals($event->event_base_fee, $schema->baseFee);
        $this->assertEquals($event->event_competition_fee, $schema->competitionFee);
        $this->assertEquals($event->event_bank, $schema->bank);
        $this->assertEquals($event->event_account_name, $schema->account);
        $this->assertEquals($event->event_organisers_address, $schema->address);
        $this->assertEquals($event->event_iban, $schema->iban);
        $this->assertEquals($event->event_swift, $schema->swift);
        $this->assertEquals($event->reference, $schema->reference);
    }

    public function testConversions()
    {
        $event = new Event();
        $event->event_base_fee = "aaa";
        $event->event_competition_fee = -1;
        $schema = new Bank($event);

        $this->assertEquals(0, $schema->baseFee);
        $this->assertEquals(-1.0, $schema->competitionFee);

        $event->event_base_fee = "10.12";
        $event->event_competition_fee = false;
        $schema = new Bank($event);

        $this->assertEquals(10.12, $schema->baseFee);
        $this->assertEquals(0, $schema->competitionFee);

    }
}
