<?php

namespace Tests\Unit\App\Models;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Event;
use App\Models\Weapon;
use Tests\Support\Data\Competition as Data;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class CompetitionTest extends TestCase
{
    public function fixtures()
    {
        Data::create();
    }

    public function testRelations()
    {
        $competitions = Competition::where('competition_id', Data::MFCAT1)->get();
        $this->assertCount(1, $competitions);
        $this->assertInstanceOf(BelongsTo::class, $competitions[0]->event());
        $this->assertInstanceOf(Event::class, $competitions[0]->event()->first());
        $this->assertInstanceOf(Event::class, $competitions[0]->event);
        $this->assertInstanceOf(BelongsTo::class, $competitions[0]->category());
        $this->assertInstanceOf(Category::class, $competitions[0]->category()->first());
        $this->assertInstanceOf(Category::class, $competitions[0]->category);
        $this->assertInstanceOf(BelongsTo::class, $competitions[0]->weapon());
        $this->assertInstanceOf(Weapon::class, $competitions[0]->weapon()->first());
        $this->assertInstanceOf(Weapon::class, $competitions[0]->weapon);
    }
}
