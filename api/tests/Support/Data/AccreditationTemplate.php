<?php

namespace Tests\Support\Data;

use DB;
use App\Models\AccreditationTemplate as Model;
use Carbon\Carbon;

class AccreditationTemplate extends Fixture
{
    public const ATHLETE = 1;
    public const ORG = 2;
    public const COUNTRY = 3;
    public const REFEREE = 4;
    public const DEF1 = 10;
    public const NOSUCHID = 3883;

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
        Event::create();
        self::booted();

        Model::create([
            'id' => self::ATHLETE,
            'name' => 'Athlete',
            'content' => '{"roles":["0"]}',
            'event_id' => Event::EVENT1,
            'is_default' => 'N',
        ])->save();

        Model::create([
            'id' => self::ORG,
            'name' => 'Organisation',
            'content' => '{"roles":["21","18","16","13","9","10","17","12","11","8","20","14","22"]}',
            'event_id' => Event::EVENT1,
            'is_default' => 'N',
        ])->save();

        Model::create([
            'id' => self::COUNTRY,
            'name' => 'Fed',
            'content' => '{"roles":["4","2","5","19","6"]}',
            'event_id' => Event::EVENT1,
            'is_default' => 'N',
        ])->save();

        Model::create([
            'id' => self::REFEREE,
            'name' => 'Referee',
            'content' => '{"roles":["7"]}',
            'event_id' => Event::EVENT1,
            'is_default' => 'N',
        ])->save();

        Model::create([
            'id' => self::DEF1,
            'name' => 'Default',
            'content' => '{"roles":["7"]}',
            'event_id' => null,
            'is_default' => 'Y',
        ])->save();
    }
}
