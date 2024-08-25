<?php

namespace Tests\Unit\App\Models;

use App\Models\DeviceUser;
use App\Models\Follow;
use App\Models\Fencer;
use Tests\Support\Data\Fencer as FencerData;
use Tests\Support\Data\DeviceUser as UserData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\Unit\TestCase;

class FollowTest extends TestCase
{
    public function testRelations()
    {
        $follow = new Follow();
        $follow->device_user_id = UserData::DEVICEUSER1;
        $follow->fencer_id = FencerData::MCAT1;
        $follow->save();

        $this->assertInstanceOf(Follow::class, $follow);

        $this->assertInstanceOf(BelongsTo::class, $follow->fencer());
        $this->assertInstanceOf(Fencer::class, $follow->fencer()->first());
        $this->assertInstanceOf(Fencer::class, $follow->fencer);

        $this->assertInstanceOf(BelongsTo::class, $follow->user());
        $this->assertInstanceOf(DeviceUser::class, $follow->user()->first());
        $this->assertInstanceOf(DeviceUser::class, $follow->user);
    }

    public function testSettingPreferences()
    {
        $follow = new Follow();
        $follow->isBlocked(true);
        $this->assertEquals(true, $follow->preferences['blocked']);
        $this->assertEquals(true, $follow->isBlocked());
        $follow->isBlocked(false);
        $this->assertTrue(!isset($follow->preferences['blocked']));

        $follow->setPreference('handout', true);
        $this->assertEquals(true, $follow->preferences['handout']);
        $this->assertEquals(true, $follow->triggersOnEvent('handout'));
        $follow->setPreference('handout', false);
        $this->assertTrue(!isset($follow->preferences['handout']));

        $follow->setPreference('checkin', true);
        $this->assertEquals(true, $follow->preferences['checkin']);
        $this->assertEquals(true, $follow->triggersOnEvent('checkin'));
        $follow->setPreference('checkin', false);
        $this->assertTrue(!isset($follow->preferences['checkin']));

        $follow->setPreference('checkout', true);
        $this->assertEquals(true, $follow->preferences['checkout']);
        $this->assertEquals(true, $follow->triggersOnEvent('checkout'));
        $follow->setPreference('checkout', false);
        $this->assertTrue(!isset($follow->preferences['checkout']));

        $follow->setPreference('ranking', true);
        $this->assertEquals(true, $follow->preferences['ranking']);
        $this->assertEquals(true, $follow->triggersOnEvent('ranking'));
        $follow->setPreference('ranking', false);
        $this->assertTrue(!isset($follow->preferences['ranking']));

        $follow->setPreference('result', true);
        $this->assertEquals(true, $follow->preferences['result']);
        $this->assertEquals(true, $follow->triggersOnEvent('result'));
        $follow->setPreference('result', false);
        $this->assertTrue(!isset($follow->preferences['result']));

        $follow->setPreference('register', true);
        $this->assertEquals(true, $follow->preferences['register']);
        $this->assertEquals(true, $follow->triggersOnEvent('register'));
        $follow->setPreference('register', false);
        $this->assertTrue(!isset($follow->preferences['register']));
    }
}
