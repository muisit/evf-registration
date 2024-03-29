<?php

namespace Tests\Support\Data;

use DB;
use App\Models\DeviceUser as Model;
use Carbon\Carbon;

class DeviceUser extends Fixture
{
    public const DEVICEUSER1 = 1;
    public const DEVICEUSER2 = 2;
    public const NOSUCHID = 9692;

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
            'id' => self::DEVICEUSER1,
            'uuid' => 'this-is-a-uuid',
            'email' => 'user@example.org',
            'password' => 'encrypted',
            'preferences' => '{}',
            'fencer_id' => Fencer::MCAT1,
            'created_at' => '2020-01-01 12:34:56',
            'updated_at' => '2020-01-01 12:34:56',
        ])->save();

        Model::create([
            'id' => self::DEVICEUSER2,
            'uuid' => 'this-is-also-a-uuid',
            'email' => null,
            'password' => null,
            'preferences' => null,
            'fencer_id' => null,
            'created_at' => '2020-01-01 12:34:56',
            'updated_at' => '2020-01-01 12:34:56',
        ])->save();
    }
}
