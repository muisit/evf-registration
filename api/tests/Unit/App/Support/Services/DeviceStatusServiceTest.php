<?php

namespace Tests\Unit\App\Support;

use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Models\Follow;
use App\Support\Services\DeviceStatusService;
use Tests\Support\Data\DeviceUser as UserData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DeviceStatusServiceTest extends TestCase
{
    public function testHandle()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $service = new DeviceStatusService();
        $result = $service->handle();
        $this->assertEquals('', $result->id);
        $this->assertEquals(0, $result->feed->count);
        $this->assertEquals('', $result->feed->last);
        $this->assertEquals(0, $result->calendar->count);
        $this->assertEquals('2024-02-01', $result->calendar->last);
        $this->assertEquals(1, $result->results->count); // one event with results
        $this->assertEquals(Carbon::now()->addDays(30)->toDateString(), $result->results->last);
        $this->assertEquals(0, $result->ranking->count);
        $this->assertEquals('', $result->ranking->last);
        $this->assertCount(0, $result->followers);
        $this->assertCount(0, $result->following);

        $date = Carbon::now()->subDays(20)->toDateTimeString();
        DeviceFeed::where('id', '>', 0)->update(['updated_at' => $date]);
        $result = $service->handle();
        $this->assertEquals(4, $result->feed->count);
        $this->assertEquals($date . '.000000', $result->feed->last);
    }

    public function testFollowers()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $service = new DeviceStatusService();

        $follower = new Follow();
        $follower->device_user_id = UserData::DEVICEUSER2;
        $follower->fencer_id = $user->fencer_id;
        $follower->feedHandout(true);
        $follower->feedRegister(true);
        $follower->save();

        $follower = new Follow();
        $follower->device_user_id = UserData::DEVICEUSER1;
        $follower->fencer_id = FencerData::WCAT5;
        $follower->feedHandout(true);
        $follower->feedRegister(true);
        $follower->save();

        $result = $service->handle();
        $this->assertCount(1, $result->followers);
        $this->assertCount(1, $result->following);
    }

    public function testBlockEffect()
    {
        $user = DeviceUser::find(UserData::DEVICEUSER1);
        Auth::login($user);
        $service = new DeviceStatusService();

        $follower = new Follow();
        $follower->device_user_id = UserData::DEVICEUSER2;
        $follower->fencer_id = $user->fencer_id;
        $follower->isBlocked(true);
        $follower->feedHandout(true);
        $follower->feedRegister(true);
        $follower->save();

        $follower = new Follow();
        $follower->device_user_id = UserData::DEVICEUSER1;
        $follower->fencer_id = FencerData::WCAT5;
        $follower->isBlocked(true);
        $follower->feedHandout(true);
        $follower->feedRegister(true);
        $follower->save();

        $result = $service->handle();
        $this->assertCount(0, $result->followers);
        $this->assertCount(1, $result->following);
    }
}
