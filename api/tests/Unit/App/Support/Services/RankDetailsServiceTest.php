<?php

namespace Tests\Unit\App\Support;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Weapon;
use App\Models\Fencer;
use App\Models\Result;
use App\Support\Services\RankingStoreService;
use App\Support\Services\RankDetailsService;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;

class RankDetailsServiceTest extends TestCase
{
    public function testGenerate()
    {
        $service = new RankingStoreService();
        $service->handle();

        $fencer = Fencer::find(FencerData::MCAT1);
        $service2 = new RankDetailsService($fencer, Weapon::find(Weapon::MF));
        $output = $service2->generate();
        $this->assertTrue(is_object($output));
        $this->assertEquals($fencer->uuid, $output->fencer);
        $this->assertEquals("MF", $output->weapon);
        $this->assertEquals("1", $output->category);
        $this->assertEquals("1", $output->position);
        $this->assertEquals("97.1", $output->points);
        $this->assertCount(1, $output->results);
    }
}
