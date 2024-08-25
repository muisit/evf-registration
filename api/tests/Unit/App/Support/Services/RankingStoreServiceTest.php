<?php

namespace Tests\Unit\App\Support;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Weapon;
use App\Models\Ranking;
use App\Models\RankingPosition;
use App\Models\Result;
use App\Support\Services\RankingStoreService;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

class RankingStoreServiceTest extends TestCase
{
    public function testGenerate()
    {
        Queue::fake();
        $service = new RankingStoreService();
        $service->handle();

        $rankdate = Carbon::now()->addDays(35)->toDateString();
        $rankings = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $rankings);
        $ranking = $rankings[0];
        $this->assertNotEmpty($ranking);
        $this->assertEquals(EventData::EVENT1, $ranking->event_id);
        $this->assertEquals(EventData::EVENT1, $ranking->event->getKey());
        $this->assertEquals($rankdate, (new Carbon($ranking->ranking_date))->toDateString());
        $this->assertCount(3, $ranking->positions()->get());

        $this->assertCount(3, $rankings[0]->positions);
        $this->assertCount(1, $rankings[1]->positions);
        $this->assertCount(1, $rankings[2]->positions);
        $this->assertCount(1, $rankings[3]->positions);
        $this->assertCount(1, $rankings[4]->positions);
        $this->assertCount(1, $rankings[5]->positions);
        $this->assertCount(1, $rankings[6]->positions);
        $this->assertCount(2, $rankings[7]->positions);

        // never any results for the following groups
        $this->assertCount(0, Ranking::where('category_id', Category::CAT5)->get());
        $this->assertCount(0, Ranking::where('category_id', Category::TEAM)->get());
        $this->assertCount(0, Ranking::where('category_id', Category::GVET)->get());
    }

    public function testRegenerate()
    {
        Queue::fake();
        $service = new RankingStoreService();
        $service->handle();

        $rankdate = Carbon::now()->addDays(35)->toDateString();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->first();
        $this->assertNotEmpty($ranking);
        $this->assertEquals(EventData::EVENT1, $ranking->event_id);
        $this->assertEquals(EventData::EVENT1, $ranking->event->getKey());
        $this->assertEquals($rankdate, (new Carbon($ranking->ranking_date))->toDateString());

        $event = Event::find(EventData::EVENT1);
        $event->event_open = Carbon::now()->subDays(30)->toDateString();
        $event->save();
        $service = new RankingStoreService();
        $service->handle();

        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $ranking);
        $this->assertNotEmpty($ranking[0]);
        $this->assertEquals(EventData::EVENT1, $ranking[0]->event_id);
        $this->assertEquals(EventData::EVENT1, $ranking[0]->event->getKey());
        // rank date does not change
        $this->assertEquals($rankdate, (new Carbon($ranking[0]->ranking_date))->toDateString());
    }

    public function testTwoEvents()
    {
        Queue::fake();
        $service = new RankingStoreService();
        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $ranking);

        $event = $this->createEvent(Carbon::now()->subDays(300)->toDateString());
        $comp = $this->createCompetition($event, Category::CAT1, Weapon::MF);
        $result1 = $this->createResult($comp->getKey(), FencerData::MCAT1, 1, 120);
        $result2 = $this->createResult($comp->getKey(), FencerData::MCAT2, 2, 100);

        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $ranking);
    }

    public function testTwoEvents2()
    {
        Queue::fake();
        $service = new RankingStoreService();
        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $ranking);

        $event = $this->createEvent(Carbon::now()->addDays(300)->toDateString());
        $comp = $this->createCompetition($event, Category::CAT1, Weapon::MF);
        $result1 = $this->createResult($comp->getKey(), FencerData::MCAT1, 1, 120);
        $result2 = $this->createResult($comp->getKey(), FencerData::MCAT2, 2, 100);

        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('ranking_date')->get();
        $this->assertCount(12, $ranking); // 4 additional mens foil rankings
        $this->assertEquals($event->getKey(), $ranking[8]->event_id);
        $this->assertEquals($event->getKey(), $ranking[9]->event_id);
        $this->assertEquals($event->getKey(), $ranking[10]->event_id);
        $this->assertEquals($event->getKey(), $ranking[11]->event_id);
    }

    public function testRegenerateTwoEvents2()
    {
        Queue::fake();
        $service = new RankingStoreService();
        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(8, $ranking);

        $event = $this->createEvent(Carbon::now()->addDays(300)->toDateString());
        $comp = $this->createCompetition($event, Category::CAT1, Weapon::MF);
        $result1 = $this->createResult($comp->getKey(), FencerData::MCAT1, 1, 120);
        $result2 = $this->createResult($comp->getKey(), FencerData::MCAT2, 2, 100);

        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('ranking_date')->get();
        $this->assertCount(12, $ranking);
        $this->assertEquals($event->getKey(), $ranking[8]->event_id);
        $this->assertEquals($event->getKey(), $ranking[9]->event_id);
        $this->assertEquals($event->getKey(), $ranking[10]->event_id);
        $this->assertEquals($event->getKey(), $ranking[11]->event_id);

        $result2->result_place = 3;
        $result2->save();
        $result3 = $this->createResult($comp->getKey(), FencerData::WCAT1, 5, 80);
        $service->handle();
        $ranking = Ranking::where('id', '>', 0)->orderBy('ranking_date')->get();
        $this->assertCount(12, $ranking);
        $this->assertEquals($event->getKey(), $ranking[8]->event_id);
        $this->assertEquals($event->getKey(), $ranking[9]->event_id);
        $this->assertEquals($event->getKey(), $ranking[10]->event_id);
        $this->assertEquals($event->getKey(), $ranking[11]->event_id);
    }

    private function createResult($comp, $fencer, $pos, $points)
    {
        $result = new Result();
        $result->result_competition = $comp;
        $result->result_fencer = $fencer;
        $result->result_place = $pos;
        $result->result_points = $points;
        $result->result_entry = 0;
        $result->result_de_points = 0;
        $result->result_podium_points = 0;
        $result->result_total_points = $points;
        $result->result_in_ranking = 'Y';
        $result->save();
        return $result;
    }

    private function createCompetition($event, $cat, $weapon)
    {
        $c = new Competition();
        $c->competition_event = $event->getKey();
        $c->competition_weapon = $weapon;
        $c->competition_category = $cat;
        $c->competition_opens = $event->event_open;
        $c->competition_weapon_check = $event->event_open;
        $c->save();
        return $c;
    }

    private function createEvent($date)
    {
        $event = new Event();
        $event->event_name = 'Test Event ' . $date;
        $event->event_open = $date;
        $event->event_duration = 2;
        $event->event_year = 2020;
        $event->event_type = EventType::INDIVIDUAL;
        $event->event_country = Country::GER;
        $event->event_currency_symbol = 'E';
        $event->event_currency_name = 'EUR';
        $event->event_bank = '';
        $event->event_account_name = '';
        $event->event_organisers_address = '';
        $event->event_iban = '';
        $event->event_swift = '';
        $event->event_reference = '';
        $event->event_in_ranking = 'Y';
        $event->save();
        return $event;
    }
}
