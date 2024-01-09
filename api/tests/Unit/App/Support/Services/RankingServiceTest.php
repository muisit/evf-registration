<?php

namespace Tests\Unit\App\Support;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Weapon;
use App\Models\Fencer;
use App\Support\Services\RankingService;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;

class RankingServiceTest extends TestCase
{
    public function testGenerate()
    {
        $cat = Category::find(Category::CAT1);
        $wpn = Weapon::find(Weapon::MF);
        $service = new RankingService($cat, $wpn);
        $results = $service->generate();
        $this->assertCount(3, $results);
        $this->assertEquals(1, $results[0]['pos']);
        $this->assertEquals(2, $results[1]['pos']);
        $this->assertEquals(3, $results[2]['pos']);
        $this->assertEquals(FencerData::MCAT1, $results[0]['id']);
        $this->assertEquals(FencerData::MCAT1B, $results[1]['id']);
        $this->assertEquals(FencerData::MCAT1C, $results[2]['id']);
    }

    public function testGenerate2()
    {
        $cat = Category::find(Category::CAT1);
        $wpn = Weapon::find(Weapon::WS);
        $service = new RankingService($cat, $wpn);
        $results = $service->generate();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]['pos']);
        $this->assertEquals(FencerData::WCAT1, $results[0]['id']);

        $cat = Category::find(Category::CAT2);
        $service = new RankingService($cat, $wpn);
        $results = $service->generate();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]['pos']);
        $this->assertEquals(FencerData::WCAT2, $results[0]['id']);

        $cat = Category::find(Category::CAT3);
        $service = new RankingService($cat, $wpn);
        $results = $service->generate();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]['pos']);
        $this->assertEquals(FencerData::WCAT3, $results[0]['id']);

        $cat = Category::find(Category::CAT4);
        $service = new RankingService($cat, $wpn);
        $results = $service->generate();
        $this->assertCount(2, $results);
        $this->assertEquals(1, $results[0]['pos']);
        $this->assertEquals(2, $results[1]['pos']);
        $this->assertEquals(FencerData::WCAT4, $results[0]['id']);
        $this->assertEquals(FencerData::WCAT5, $results[1]['id']);
    }
}
