<?php

namespace Tests\Unit\App\Http\Controllers\Ranking;

use App\Models\Ranking;
use App\Jobs\CreateRanking;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class ResetTest extends TestCase
{
    public function testRoute()
    {
        Queue::fake();
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->insert(['option_id' => 2, 'option_name' => 'evf_internal_user', 'option_value' => UserData::TESTUSER]);
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->insert(['option_id' => 3, 'option_name' => 'evf_internal_key', 'option_value' => 'aaaa']);

        $response = $this->post('/fe/ranking/reset', [], ['Authorization' => 'Bearer aaaa'])->assertStatus(200);
        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertTrue(isset($output['status']));
        $this->assertEquals("ok", $output['status']);
        $this->assertTrue(!isset($output['message']));

        $date = Carbon::now()->addDays(35)->toDateString();
        $ranking = Ranking::where('id', '>', 0)->orderBy('category_id')->orderBy('weapon_id')->get();
        $this->assertCount(0, $ranking); // nothing created yet
        Queue::assertPushed(CreateRanking::class, 1);
    }

    public function testAuth()
    {
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->insert(['option_id' => 2, 'option_name' => 'evf_internal_user', 'option_value' => UserData::TESTUSER]);
        DB::table(env('WPDBPREFIX', 'wp_') . 'options')
            ->insert(['option_id' => 3, 'option_name' => 'evf_internal_key', 'option_value' => 'bbbb']);

        $this->post('/fe/ranking/reset', [], ['Authorization' => 'Bearer aaaa'])->assertStatus(401);
    }
}
