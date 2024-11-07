<?php

namespace Tests\Unit\App\Models;

use App\Models\Country;
use App\Models\Fencer;
use Tests\Support\Data\Fencer as FencerData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class FencerTest extends TestCase
{
    public function fixtures()
    {
        FencerData::create();
    }

    public function testRelations()
    {
        $fencer = Fencer::where('fencer_id', FencerData::MCAT1)->first();
        $this->assertNotEmpty($fencer);
        $this->assertInstanceOf(Fencer::class, $fencer);
        $this->assertInstanceOf(BelongsTo::class, $fencer->country());
        $this->assertInstanceOf(Country::class, $fencer->country()->first());
        $this->assertInstanceOf(Country::class, $fencer->country);
    }

    public function testTriggersEvent()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $this->assertTrue($fencer->triggersEvent("result"));
        $this->assertFalse($fencer->triggersEvent("nosuchevent"));

        $fencer = Fencer::find(FencerData::MCAT5);
        $this->assertFalse($fencer->triggersEvent("result"));
        $this->assertFalse($fencer->triggersEvent("nosuchevent"));
    }
}
