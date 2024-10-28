<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Follow as Model;
use Carbon\Carbon;

class Follow extends Fixture
{
    public const DEVICEFOLLOWER2 = 29821;
    public const DEVICEFOLLOWER3 = 39821;
    public const DEVICEFOLLOWER4 = 49821;
    public const DEVICEFOLLOWER5 = 59821;

    protected static function wasBooted($cls)
    {
        $count = Model::where('id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        self::booted();
        Model::create([
            'id' => self::DEVICEFOLLOWER2,
            'preferences' => ["handout" => true,"register" => true,"ranking" => true,"result" => true,"checkin" => true,"checkout" => true],
            'device_user_id' => DeviceUser::DEVICEUSER2,
            'fencer_id' => Fencer::MCAT1
        ])->save();

        Model::create([
            'id' => self::DEVICEFOLLOWER3,
            'preferences' => ["handout" => true,"register" => true,"ranking" => true,"result" => true,"checkin" => true,"checkout" => true],
            'device_user_id' => DeviceUser::DEVICEUSER3,
            'fencer_id' => Fencer::MCAT1
        ])->save();

        Model::create([
            'id' => self::DEVICEFOLLOWER4,
            'preferences' => ["handout" => true,"register" => true,"ranking" => true,"result" => true,"checkin" => true,"checkout" => true],
            'device_user_id' => DeviceUser::DEVICEUSER1,
            'fencer_id' => Fencer::MCAT3
        ])->save();

        Model::create([
            'id' => self::DEVICEFOLLOWER5,
            'preferences' => ["handout" => true,"register" => true,"ranking" => true,"result" => true,"checkin" => true,"checkout" => true],
            'device_user_id' => DeviceUser::DEVICEUSER1,
            'fencer_id' => Fencer::MCAT2
        ])->save();
    }
}
