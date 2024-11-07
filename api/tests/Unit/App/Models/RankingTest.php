<?php

namespace Tests\Unit\App\Models;

use App\Models\Ranking;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Event;
use App\Models\Fencer;
use App\Models\RankingPosition;
use App\Models\Weapon;
use App\Support\Services\RankingStoreService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\Unit\TestCase;

class RankingTest extends TestCase
{
    public function testRelations()
    {
        $rankings = Ranking::all();
        $this->assertCount(0, $rankings);

        $service = new RankingStoreService();
        $service->handle();
        $rankings = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $rankings);
        $ranking = $rankings[0];

        $this->assertInstanceOf(BelongsTo::class, $ranking->event());
        $this->assertInstanceOf(Event::class, $ranking->event()->first());
        $this->assertInstanceOf(Event::class, $ranking->event);

        $this->assertInstanceOf(BelongsTo::class, $ranking->weapon());
        $this->assertInstanceOf(Weapon::class, $ranking->weapon()->first());
        $this->assertInstanceOf(Weapon::class, $ranking->weapon);

        $this->assertInstanceOf(BelongsTo::class, $ranking->category());
        $this->assertInstanceOf(Category::class, $ranking->category()->first());
        $this->assertInstanceOf(Category::class, $ranking->category);

        $this->assertInstanceOf(HasMany::class, $ranking->positions());
        $this->assertInstanceOf(RankingPosition::class, $ranking->positions()->first());
        $this->assertInstanceOf(RankingPosition::class, $ranking->positions[0]);

    }
}
