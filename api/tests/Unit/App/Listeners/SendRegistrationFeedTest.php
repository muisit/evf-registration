<?php

namespace Tests\Unit\App\Models;

use App\Events\RegisterForEvent;
use App\Models\AccreditationDocument;
use App\Models\Competition;
use App\Models\Event;
use App\Models\DeviceFeed;
use App\Models\DeviceUser;
use App\Models\Fencer;
use App\Models\Follow;
use App\Models\RankingPosition;
use App\Listeners\SendRegistrationFeed;
use App\Support\Services\RankingStoreService;
use Tests\Support\Data\AccreditationDocument as DocData;
use Tests\Support\Data\Competition as CompetitionData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\Event as EventData;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Unit\TestCase;

class SendRegistrationFeedTest extends TestCase
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

    public function testHandle()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, false);

        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);

        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "register");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", true);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the followers
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(1, $user3->feeds);
    }

    public function testHandleUser()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, false);

        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "register");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", false);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", false);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(6, $feeds); // 1 for the user and 0 for the followers
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
    }

    public function testHandleFollowers()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, false);

        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "register");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", false);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the followers
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
    }

    public function testHandleNone()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, false);

        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "none");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", true);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(6, $feeds); // not generated for followers, but always for the user
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
    }

    public function testHandleBlock()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, false);

        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);
        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "register");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "blocked", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", true);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(1, $user3->feeds);
    }

    public function testHandleUnregistration()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, true);

        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);

        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "register");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", true);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the followers
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(1, $user3->feeds);
    }

    public function testCombinations()
    {
        $comp = Competition::find(CompetitionData::MFCAT2);
        $fencer = Fencer::find(FencerData::MCAT1);
        $ev = new RegisterForEvent($fencer, $comp, false);

        $this->setForFollowers(DeviceUserData::DEVICEUSER1, "register");
        $this->setForFollowing(DeviceUserData::DEVICEUSER2, FencerData::MCAT1, "register", true);
        $this->setForFollowing(DeviceUserData::DEVICEUSER3, FencerData::MCAT1, "register", true);

        $listener = new SendRegistrationFeed();
        $listener->handle($ev);
        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // 1 for the user and 1 for the followers
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(1, $user3->feeds);

        $ev = new RegisterForEvent($fencer, $comp, true);
        $listener->handle($ev);

        $feeds = DeviceFeed::all();
        $this->assertCount(5, $feeds); // feeds are removed
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(5, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(3, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(0, $user3->feeds);

        $ev = new RegisterForEvent($fencer, $comp, false);
        $listener->handle($ev);

        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // feeds are removed
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(1, $user3->feeds);

        // re-registration does not create new feeds
        $listener->handle($ev);

        $feeds = DeviceFeed::all();
        $this->assertCount(7, $feeds); // feeds are removed
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $this->assertCount(6, $user1->feeds);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $this->assertCount(4, $user2->feeds);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $this->assertCount(1, $user3->feeds);
    }
}
