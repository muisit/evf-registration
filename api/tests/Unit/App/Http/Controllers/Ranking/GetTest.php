<?php

namespace Tests\Unit\App\Http\Controllers\Ranking;

use App\Support\Services\RankingStoreService;
use Tests\Unit\TestCase;

class GetTest extends TestCase
{
    public function testRoute()
    {
        // create a ranking version
        $service = new RankingStoreService();
        $service->handle();

        $response = $this->get('/ranking/mf/1');

        $output = $response->json();
        $this->assertTrue($output !== false);
        $this->assertTrue(is_array($output));
        $this->assertEquals('1', $output['category']);
        $this->assertEquals('MF', $output['weapon']);
        $this->assertNotEmpty($output['positions']);
        $this->assertCount(3, $output['positions']);
        // do not test the sorting order, we do not guarantee it

        $response = $this->get('/ranking/mf/2')->assertStatus(200);
        $response = $this->get('/ranking/mf/3')->assertStatus(200);
        $response = $this->get('/ranking/mf/4')->assertStatus(200);
        $response = $this->get('/ranking/me/1')->assertStatus(404); // no epee results in test set
        $response = $this->get('/ranking/me/2')->assertStatus(404);
        $response = $this->get('/ranking/me/3')->assertStatus(404);
        $response = $this->get('/ranking/me/4')->assertStatus(404);
        $response = $this->get('/ranking/ms/1')->assertStatus(404); // no sabre results in test set
        $response = $this->get('/ranking/ms/2')->assertStatus(404);
        $response = $this->get('/ranking/ms/3')->assertStatus(404);
        $response = $this->get('/ranking/ms/4')->assertStatus(404);
        $response = $this->get('/ranking/wf/1')->assertStatus(404); // no womens foil results in test set
        $response = $this->get('/ranking/wf/2')->assertStatus(404);
        $response = $this->get('/ranking/wf/3')->assertStatus(404);
        $response = $this->get('/ranking/wf/4')->assertStatus(404);
        $response = $this->get('/ranking/we/1')->assertStatus(404); // no womens epee results in test set
        $response = $this->get('/ranking/we/2')->assertStatus(404);
        $response = $this->get('/ranking/we/3')->assertStatus(404);
        $response = $this->get('/ranking/we/4')->assertStatus(404);
        $response = $this->get('/ranking/ws/1')->assertStatus(200);
        $response = $this->get('/ranking/ws/2')->assertStatus(200);
        $response = $this->get('/ranking/ws/3')->assertStatus(200);
        $response = $this->get('/ranking/ws/4')->assertStatus(200);

        $response = $this->get('/ranking/mf/5')->assertStatus(404);
        $response = $this->get('/ranking/me/5')->assertStatus(404);
        $response = $this->get('/ranking/ms/5')->assertStatus(404);
        $response = $this->get('/ranking/wf/5')->assertStatus(404);
        $response = $this->get('/ranking/we/5')->assertStatus(404);
        $response = $this->get('/ranking/ws/5')->assertStatus(404);

        $response = $this->get('/ranking/none/1')->assertStatus(404);
        $response = $this->get('/ranking/mf/0')->assertStatus(404);
        $response = $this->get('/ranking/what/ever')->assertStatus(404);
        $response = $this->get('/ranking')->assertStatus(404);
    }
}
