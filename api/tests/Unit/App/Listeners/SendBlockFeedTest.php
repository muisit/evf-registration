<?php

namespace Tests\Unit\App\Models;

use App\Events\FollowEvent;
use App\Models\AccreditationDocument;
use App\Models\Event;
use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Models\Fencer;
use App\Models\Follow;
use App\Listeners\SendBlockFeed;
use Tests\Support\Data\AccreditationDocument as DocData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;

class SendBlockFeedTest extends TestCase
{
    private function setForFollowers($id, $event)
    {
        // this enables this event for the followers
        $user = DeviceUser::find($id);
        $user->preferences = ["account" => ["followers" => [$event]]];
        $user->save();
    }

    private function setForFollowing($user, $fencer, $event, $value)
    {
        // this enables this event for reception by the followers
        $user = Follow::where('device_user_id', $user)->where('fencer_id', $fencer)->first();
        $user->setPreference($event, $value);
        $user->save();
    }

    public function testBlock()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $ev = new FollowEvent($fencer, $user2, false);

        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $listener = new SendBlockFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the follower
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
    }

    public function testUnblock()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $ev = new FollowEvent($fencer, $user2, true);

        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $listener = new SendBlockFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the follower
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
    }

    public function testBlockAndUnblock()
    {
        $fencer = Fencer::find(FencerData::MCAT1);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $ev = new FollowEvent($fencer, $user2, false);

        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $listener = new SendBlockFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the follower
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);

        $ev = new FollowEvent($fencer, $user2, true);
        $listener = new SendBlockFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(9, $feeds); // 1 for the user and 1 for the follower
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(7, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(5, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
    }
}
