<?php

namespace Tests\Unit\App\Support;

use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Support\Services\DeviceStatusService;
use Tests\Support\Data\DeviceUser as UserData;
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
        $this->assertEquals('', $result->calendar->last);
        $this->assertEquals(1, $result->results->count); // one event with results
        $this->assertEquals(Carbon::now()->addDays(30)->toDateString(), $result->results->last);
        $this->assertEquals(0, $result->ranking->count);
        $this->assertEquals('', $result->ranking->last);

        $date = Carbon::now()->subDays(20)->toDateTimeString();
        DeviceFeed::where('id', '>', 0)->update(['updated_at' => $date]);
        $result = $service->handle();
        $this->assertEquals(4, $result->feed->count);
        $this->assertEquals($date . '.000000', $result->feed->last);
    }
}
