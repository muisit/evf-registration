<?php

namespace Tests\Unit\App\Models\Policies;

use App\Models\AccreditationUser;
use App\Models\DeviceUser;
use App\Models\Follow;
use App\Models\WPUser;
use App\Models\Policies\Follow as Policy;
use Tests\Support\Data\AccreditationUser as AccreditationUserData;
use Tests\Support\Data\Follow as FollowData;
use Tests\Support\Data\DeviceUser as DeviceUserData;
use Tests\Support\Data\WPUser as UserData;
use Tests\Support\Data\Event as EventData;
use Tests\Unit\TestCase;
use Carbon\Carbon;

class FollowTest extends TestCase
{
    public function testView()
    {
        $policy = new Policy();
        $admin = WPUser::where("ID", UserData::TESTUSER)->first();
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $follower = Follow::find(FollowData::DEVICEFOLLOWER2);

        // only the follower and the followed can view
        $this->assertFalse($policy->view($admin, $follower));
        $this->assertFalse($policy->view($admin2, $follower));
        $this->assertTrue($policy->view($user1, $follower));
        $this->assertTrue($policy->view($user2, $follower));
        $this->assertFalse($policy->view($user3, $follower));
    }

    public function testCreate()
    {
        $policy = new Policy();
        $admin = WPUser::where("ID", UserData::TESTUSER)->first();
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $follower = Follow::find(FollowData::DEVICEFOLLOWER2);

        // any device user can create
        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->create($admin2));
        $this->assertTrue($policy->create($user1));
        $this->assertTrue($policy->create($user2));
        $this->assertTrue($policy->create($user3));
    }

    public function testUpdate()
    {
        $policy = new Policy();
        $admin = WPUser::where("ID", UserData::TESTUSER)->first();
        $admin2 = AccreditationUser::find(AccreditationUserData::ADMIN);
        $user1 = DeviceUser::find(DeviceUserData::DEVICEUSER1);
        $user2 = DeviceUser::find(DeviceUserData::DEVICEUSER2);
        $user3 = DeviceUser::find(DeviceUserData::DEVICEUSER3);
        $follower = Follow::find(FollowData::DEVICEFOLLOWER2);

        // only the following can update
        $this->assertFalse($policy->update($admin, $follower));
        $this->assertFalse($policy->update($admin2, $follower));
        $this->assertFalse($policy->update($user1, $follower));
        $this->assertTrue($policy->update($user2, $follower));
        $this->assertFalse($policy->update($user3, $follower));
    }
}
