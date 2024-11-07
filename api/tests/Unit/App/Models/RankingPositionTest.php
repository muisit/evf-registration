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

class RankingPositionTest extends TestCase
{
    public function testRelations()
    {
        $service = new RankingStoreService();
        $service->handle();
        $rankings = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $ranking = $rankings[0];
        $position = $ranking->positions[0];

        $this->assertInstanceOf(BelongsTo::class, $position->ranking());
        $this->assertInstanceOf(Ranking::class, $position->ranking()->first());
        $this->assertInstanceOf(Ranking::class, $position->ranking);

        $this->assertInstanceOf(BelongsTo::class, $position->fencer());
        $this->assertInstanceOf(Fencer::class, $position->fencer()->first());
        $this->assertInstanceOf(Fencer::class, $position->fencer);

        $this->assertTrue(is_numeric($position->position));
        $this->assertTrue(is_numeric($position->points));
        $this->assertTrue(is_array($position->settings));
    }
}
