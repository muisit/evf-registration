<?php

namespace Tests\Unit\App\Models;

use App\Models\Device;
use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Models\Follow;
use App\Models\Fencer;
use Tests\Support\Data\Device as DeviceData;
use Tests\Support\Data\DeviceFeed as DeviceFeedData;
use Tests\Support\Data\DeviceUser as Data;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\Follow as FollowData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\Unit\TestCase;

class DeviceUserTest extends TestCase
{
    public function testRelations()
    {
        $data = DeviceUser::find(Data::DEVICEUSER1);
        $this->assertNotEmpty($data);
        $this->assertNotEquals('this-is-a-uuid', $data->uuid); // overwritten at create
        $this->assertEquals('user@example.org', $data->email);
        $this->assertEquals('2020-01-01 12:34:56', $data->email_verified_at);
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        $this->assertInstanceOf(BelongsTo::class, $data->fencer());
        $this->assertInstanceOf(Fencer::class, $data->fencer);
        $this->assertEquals(FencerData::MCAT1, $data->fencer->getKey());

        $this->assertInstanceOf(HasMany::class, $data->devices());
        $this->assertCount(2, $data->devices);
        $this->assertInstanceOf(Device::class, $data->devices[0]);
        $this->assertEquals(DeviceData::DEVICE1, $data->devices[0]->getKey());

        $this->assertInstanceOf(BelongsToMany::class, $data->feeds());
        $this->assertCount(5, $data->feeds);
        $this->assertInstanceOf(DeviceFeed::class, $data->feeds[0]);
        $this->assertEquals(DeviceFeedData::FEED1, $data->feeds[0]->getKey());

        $this->assertInstanceOf(HasMany::class, $data->following());
        $this->assertCount(2, $data->following);
        $this->assertInstanceOf(Follow::class, $data->following[0]);
        $this->assertEquals(FollowData::DEVICEFOLLOWER4, $data->following[0]->getKey());
    }

    public function testSave()
    {
        $data = new DeviceUser();
        $this->assertEmpty($data->uuid);
        $this->assertEmpty($data->created_at);
        $this->assertEmpty($data->updated_at);

        $data->save();
        $this->assertNotEmpty($data->uuid);
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);

        sleep(1);
        $data->email = 'new email';
        $data->save();
        $this->assertNotEmpty($data->created_at);
        $this->assertNotEmpty($data->updated_at);
        $this->assertNotEquals($data->created_at, $data->updated_at);
    }

    public function testDelete()
    {
        $data = DeviceUser::find(Data::DEVICEUSER2);
        $this->assertCount(1, Follow::where('device_user_id', $data->getKey())->get());
        $this->assertCount(1, Device::where('device_user_id', $data->getKey())->get());
        $this->assertCount(3, $data->feeds);

        $data->delete();
        $this->assertCount(0, Follow::where('device_user_id', $data->getKey())->get());
        $this->assertCount(0, Device::where('device_user_id', $data->getKey())->get());
        $this->assertCount(0, $data->feeds()->get());
    }

    public function testAuthMethods()
    {
        $data = DeviceUser::find(Data::DEVICEUSER2);
        $this->assertEmpty($data->getAuthPassword());
        $this->assertEquals($data->email, $data->getAuthName());
        $this->assertEquals('deviceuser', $data->getAuthSessionName());
        $this->assertCount(1, $data->getAuthRoles());
        $this->assertEquals('device', $data->getAuthRoles()[0]);
    }

    public function testMerge()
    {
        $data1 = DeviceUser::find(Data::DEVICEUSER1);
        $data2 = DeviceUser::find(Data::DEVICEUSER2);

        $data2->mergeWith($data1);

        // the old user is not deleted
        $this->assertNotEmpty(DeviceUser::find(Data::DEVICEUSER1));
        $this->assertNotEmpty($data1->feeds()->get());
        // but all devices have been moved
        $this->assertEmpty($data1->devices()->get());
        // and all fencers we follow have been moved
        $this->assertCount(0, $data1->following()->get());

        // the new user has all devices
        $this->assertCount(3, $data2->devices()->get());
        // and is attached to all feeds
        $this->assertCount(5, $data2->feeds()->get());
        // and is now also following MCAT3. MCAT2 was removed from the list
        $this->assertCount(2, $data2->following()->get());
    }

    public function testTriggersEvent()
    {
        $data1 = DeviceUser::find(Data::DEVICEUSER1);
        // these are the default settings on create, not the ones in the fixture
        $this->assertTrue($data1->triggersEvent('result'));
        $this->assertTrue($data1->triggersEvent('register'));
        $this->assertTrue($data1->triggersEvent('handout'));
        $this->assertTrue($data1->triggersEvent('ranking'));
        $this->assertFalse($data1->triggersEvent('checkin'));

        $data1->preferences = array_merge($data1->preferences, ['account' => ['followers' => ['checkin', 'somethingelse', 'newitem']]]);
        $this->assertFalse($data1->triggersEvent('result'));
        $this->assertFalse($data1->triggersEvent('register'));
        $this->assertFalse($data1->triggersEvent('handout'));
        $this->assertFalse($data1->triggersEvent('ranking'));
        $this->assertTrue($data1->triggersEvent('checkin'));
        $this->assertTrue($data1->triggersEvent('somethingelse'));
        $this->assertTrue($data1->triggersEvent('newitem'));
    }
}
