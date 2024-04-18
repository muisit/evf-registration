<?php

namespace Tests\Unit\App\Http\Controllers\Device;

use App\Models\Device;
use App\Models\DeviceUser;
use Tests\Unit\TestCase;
use Tests\Support\Data\Device as DeviceData;
use Tests\Support\Data\DeviceUser as UserData;

class StatusTest extends TestCase
{
    public function testRoute()
    {
        $device = Device::find(DeviceData::DEVICE1);
        $response = $this->get('/device/status', ['Authorization' => 'Bearer ' . $device->uuid]);

        // we expect a non-empty result and a 200 status
        $output = (array) $response->json();
        $this->assertNotEmpty($output);
        $this->assertStatus(200);
        $this->assertEquals(0, $output['feed']['count']);
        $this->assertEquals($device->uuid, $output['id']);
    }

    public function testUnAuthorised()
    {
        $response = $this->get('/device/status');
        $this->assertStatus(403);

        $response = $this->get('/device/status', ['Authorization' => 'Bearer no-such-id']);
        $this->assertStatus(403);
    }
}
