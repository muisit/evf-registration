<?php

namespace Tests\Unit\App\Http\Controllers\Device;

use App\Models\Device;
use App\Models\DeviceUser;
use App\Models\Fencer;
use Tests\Unit\TestCase;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Device as DeviceData;
use Tests\Support\Data\DeviceUser as UserData;
use Tests\Support\Data\Competition as CompetitionData;
use Carbon\Carbon;

class ResultsTest extends TestCase
{
    public function testRoute()
    {
        $device = Device::find(DeviceData::DEVICE1);
        $response = $this->get('/device/results/' . CompetitionData::MFCAT1, ['Authorization' => 'Bearer ' . $device->uuid]);
        $f1 = Fencer::find(FencerData::MCAT1);
        $f2 = Fencer::find(FencerData::MCAT1B);
        $f3 = Fencer::find(FencerData::MCAT1C);

        // we expect a non-empty result and a 200 status
        $output = (array) $response->json();
        $this->assertNotEmpty($output);
        $this->assertStatus(200);
        $this->assertEquals(CompetitionData::MFCAT1, $output['id']);
        $this->assertEquals('1', $output['category']);
        $this->assertEquals('MF', $output['weapon']);
        $opens = Carbon::now()->addDays(11)->toDateString();
        $this->assertEquals($opens, $output['starts']);
        $this->assertCount(3, $output['results']);

        $this->assertTrue(isset($output['results'][0]['fencer']));
        $this->assertEquals($f1->uuid, $output['results'][0]['fencer']['id']);
        $this->assertEquals('Germany', $output['results'][0]['fencer']['country']);
        $this->assertEquals('GER', $output['results'][0]['fencer']['countryShort']);
        $this->assertEquals(1, $output['results'][0]['position']);
        $this->assertEquals(30.2, $output['results'][0]['points']);
        $this->assertEquals(10, $output['results'][0]['de']);
        $this->assertEquals(56.9, $output['results'][0]['podium']);
        $this->assertEquals(97.1, $output['results'][0]['total']);
        $this->assertEquals('Y', $output['results'][0]['status']);
        $this->assertEquals(3, $output['results'][0]['entries']);

        $this->assertTrue(isset($output['results'][1]['fencer']));
        $this->assertEquals($f2->uuid, $output['results'][1]['fencer']['id']);
        $this->assertEquals(3, $output['results'][1]['entries']);
        $this->assertEquals(2, $output['results'][1]['position']);

        $this->assertTrue(isset($output['results'][2]['fencer']));
        $this->assertEquals($f3->uuid, $output['results'][2]['fencer']['id']);
        $this->assertEquals(3, $output['results'][2]['position']);
        $this->assertEquals(3, $output['results'][2]['entries']);
    }

    public function testUnAuthorised()
    {
        $response = $this->get('/device/results/' . CompetitionData::MFCAT1);
        $this->assertStatus(403);

        $response = $this->get('/device/results/' . CompetitionData::MFCAT1, ['Authorization' => 'Bearer no-such-id']);
        $this->assertStatus(403);
    }
}
