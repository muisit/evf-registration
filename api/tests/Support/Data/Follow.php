<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Follow as Model;
use Carbon\Carbon;

class Follow extends Fixture
{
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
            'preferences' => '{}',
            'fencer_id' => Fencer::MCAT1,
            'created_at' => '2020-01-01 12:34:56',
            'updated_at' => '2020-01-01 12:34:56',
            'email_verified_at' => '2020-01-01 12:34:56',
        ])->save();

        Model::create([
            'id' => self::DEVICEUSER2,
            'uuid' => 'this-is-also-a-uuid',
            'email' => null,
            'preferences' => null,
            'fencer_id' => null,
            'created_at' => '2020-01-01 12:34:56',
            'updated_at' => '2020-01-01 12:34:56',
            'email_verified_at' => null,
        ])->save();
    }
}
