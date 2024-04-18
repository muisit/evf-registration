<?php

namespace Tests\Unit\App\Http\Controllers\Device;

use App\Models\Device;
use App\Models\DeviceUser;
use Tests\Unit\TestCase;
use Tests\Support\Data\Device as DeviceData;
use Tests\Support\Data\DeviceUser as UserData;
use Tests\Support\Data\Event as EventData;
use Carbon\Carbon;

class EventsTest extends TestCase
{
    public function testRoute()
    {
        $device = Device::find(DeviceData::DEVICE1);
        $response = $this->get('/device/events', ['Authorization' => 'Bearer ' . $device->uuid]);

        // we expect a non-empty result and a 200 status
        $output = (array) $response->json();
        $this->assertNotEmpty($output);
        $this->assertStatus(200);
        $this->assertCount(1, $output);
        $this->assertEquals(EventData::EVENT1, $output[0]['id']);
        $open = Carbon::now()->addDays(30)->toDateString();
        $close = Carbon::now()->addDays(30 + 4)->toDateString();
        $this->assertEquals($open, $output[0]['opens']);
        $this->assertEquals($close, $output[0]['closes']);
        $this->assertCount(4, $output[0]['competitions']);
    }

    public function testUnAuthorised()
    {
        $response = $this->get('/device/events');
        $this->assertStatus(403);

        $response = $this->get('/device/events', ['Authorization' => 'Bearer no-such-id']);
        $this->assertStatus(403);
    }
}
