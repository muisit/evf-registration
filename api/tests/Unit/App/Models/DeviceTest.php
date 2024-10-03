<?php

namespace Tests\Unit\App\Models;

use App\Models\Device;
use App\Models\DeviceUser;
use Tests\Support\Data\Device as Data;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class DeviceTest extends TestCase
{
    public function testRelations()
    {
        $data = Device::find(Data::DEVICE1);
        $this->assertNotEmpty($data);
        $this->assertNotEquals($data->uuid, 'this-is-a-uuid'); // overwritten at create
        $this->assertNotEmpty($data->platform);
        $this->assertEquals(DeviceUserData::DEVICEUSER1, $data->device_user_id);
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        $this->assertInstanceOf(BelongsTo::class, $data->user());
        $this->assertInstanceOf(DeviceUser::class, $data->user);
        $this->assertEquals(DeviceUserData::DEVICEUSER1, $data->user->getKey());
    }

    public function testSave()
    {
        $data = new Device();
        $data->device_user_id = DeviceUserData::DEVICEUSER1;
        $this->assertEmpty($data->uuid);
        $this->assertEmpty($data->created_at);
        $this->assertEmpty($data->updated_at);

        $data->save();
        $this->assertNotEmpty($data->uuid);
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        sleep(1);
        $data->device_user_id = DeviceUserData::DEVICEUSER2;
        $data->save();
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);
        $this->assertNotEquals($data->created_at, $data->updated_at);
    }
}
