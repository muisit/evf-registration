<?php

namespace Tests\Unit\App\Http\Controllers\Ranking;

use App\Models\Ranking;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateTest extends TestCase
{
    public function testRoute()
    {
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->insert(['option_id' => 2, 'option_name' => 'evf_internal_user', 'option_value' => UserData::TESTUSER]);
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->insert(['option_id' => 3, 'option_name' => 'evf_internal_key', 'option_value' => 'aaaa']);

        $response = $this->get('/ranking/create', ['Authorization' => 'Bearer aaaa'])->assertStatus(200);

        $date = Carbon::now()->addDays(35)->toDateString();
        $ranking = Ranking::where('id', '>', 0)->get();
        $this->assertCount(1, $ranking);
        $this->assertCount(11, $ranking[0]->positions);
        $this->assertEquals(EventData::EVENT1, $ranking[0]->event->getKey());
        $this->assertEquals($date, (new Carbon($ranking[0]->ranking_date))->toDateString());
    }
}
