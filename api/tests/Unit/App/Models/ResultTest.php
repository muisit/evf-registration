<?php

namespace Tests\Unit\App\Models;

use App\Models\Result;
use App\Models\Competition;
use App\Models\Fencer;
use Tests\Support\Data\Result as ResultData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Fencer as FencerData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class ResultTest extends TestCase
{
    public function testRelations()
    {
        $results = Result::all();
        $this->assertCount(11, $results);

        $this->assertInstanceOf(BelongsTo::class, $results[0]->competition());
        $this->assertInstanceOf(Competition::class, $results[0]->competition()->first());
        $this->assertInstanceOf(Competition::class, $results[0]->competition);

        $this->assertInstanceOf(BelongsTo::class, $results[0]->fencer());
        $this->assertInstanceOf(Fencer::class, $results[0]->fencer()->first());
        $this->assertInstanceOf(Fencer::class, $results[0]->fencer);
    }
}
