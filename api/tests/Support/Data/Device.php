<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Device as Model;
use Carbon\Carbon;

class Device extends Fixture
{
    public const DEVICE1 = 17881;
    public const DEVICE2 = 27881;
    public const DEVICE3 = 37881;
    public const NOSUCHID = 997881;

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
        Model::create(
            [
                'id' => self::DEVICE1,
                'uuid' => 'this-is-a-uuid',
                'platform' => '{"model":"random model"}',
                'device_user_id' => DeviceUser::DEVICEUSER1,
                'created_at' => '2020-01-01 12:34:56',
                'updated_at' => '2020-01-01 12:34:56',
            ],
            [
                'id' => self::DEVICE2,
                'uuid' => 'this-is-a-uuid-2',
                'platform' => '{"model":"random model"}',
                'device_user_id' => DeviceUser::DEVICEUSER1,
                'created_at' => '2020-01-01 12:34:56',
                'updated_at' => '2020-01-01 12:34:56',
            ],
            [
                'id' => self::DEVICE3,
                'uuid' => 'this-is-another-uuid',
                'platform' => '{"model":"random model2"}',
                'device_user_id' => DeviceUser::DEVICEUSER2,
                'created_at' => '2020-01-01 12:34:56',
                'updated_at' => '2020-01-01 12:34:56',
            ]
        )->save();
    }
}
