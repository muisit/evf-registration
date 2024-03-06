<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Result as Model;
use Carbon\Carbon;

class Result extends Fixture
{
    public const MFCAT1 = 1;
    public const MFCAT1B = 2;
    public const MFCAT1C = 3;
    public const MFCAT2 = 4;
    public const MFCAT3 = 5;
    public const MFCAT4 = 6;
    public const WSCAT1 = 7;
    public const WSCAT2 = 8;
    public const WSCAT3 = 9;
    public const WSCAT4 = 10;
    public const WSCAT5 = 11;

    protected static function wasBooted($cls)
    {
        $count = Model::where('result_id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        Competition::create();
        Fencer::create();
        self::booted();

        Model::create([
            'result_id' => self::MFCAT1,
            'result_competition' => Competition::MFCAT1,
            'result_fencer' => Fencer::MCAT1,
            'result_place' => 1,
            'result_points' => 30.2,
            'result_entry' => 3,
            'result_de_points' => 10,
            'result_podium_points' => 56.9,
            'result_total_points' => 97.1,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::MFCAT1B,
            'result_competition' => Competition::MFCAT1,
            'result_fencer' => Fencer::MCAT1B,
            'result_place' => 2,
            'result_points' => 28.2,
            'result_entry' => 3,
            'result_de_points' => 10,
            'result_podium_points' => 32.1,
            'result_total_points' => 70.3,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::MFCAT1C,
            'result_competition' => Competition::MFCAT1,
            'result_fencer' => Fencer::MCAT1C,
            'result_place' => 3,
            'result_points' => 1,
            'result_entry' => 3,
            'result_de_points' => 0,
            'result_podium_points' => 12,
            'result_total_points' => 13,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::MFCAT2,
            'result_competition' => Competition::MFCAT2,
            'result_fencer' => Fencer::MCAT2,
            'result_place' => 1,
            'result_points' => 14,
            'result_entry' => 3,
            'result_de_points' => 10,
            'result_podium_points' => 20,
            'result_total_points' => 44,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::MFCAT3,
            'result_competition' => Competition::MFCAT2,
            'result_fencer' => Fencer::MCAT3,
            'result_place' => 2,
            'result_points' => 8,
            'result_entry' => 3,
            'result_de_points' => 0,
            'result_podium_points' => 10,
            'result_total_points' => 18,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::MFCAT4,
            'result_competition' => Competition::MFCAT2,
            'result_fencer' => Fencer::MCAT4,
            'result_place' => 3,
            'result_points' => 1,
            'result_entry' => 3,
            'result_de_points' => 0,
            'result_podium_points' => 0,
            'result_total_points' => 1,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::WSCAT1,
            'result_competition' => Competition::WSCAT1,
            'result_fencer' => Fencer::WCAT1,
            'result_place' => 1,
            'result_points' => 16,
            'result_entry' => 5,
            'result_de_points' => 20,
            'result_podium_points' => 20,
            'result_total_points' => 56,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::WSCAT2,
            'result_competition' => Competition::WSCAT1,
            'result_fencer' => Fencer::WCAT2,
            'result_place' => 2,
            'result_points' => 8,
            'result_entry' => 5,
            'result_de_points' => 15,
            'result_podium_points' => 10,
            'result_total_points' => 33,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::WSCAT3,
            'result_competition' => Competition::WSCAT1,
            'result_fencer' => Fencer::WCAT3,
            'result_place' => 3,
            'result_points' => 4,
            'result_entry' => 5,
            'result_de_points' => 10,
            'result_podium_points' => 1,
            'result_total_points' => 15,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::WSCAT4,
            'result_competition' => Competition::WSCAT1,
            'result_fencer' => Fencer::WCAT4,
            'result_place' => 3,
            'result_points' => 4,
            'result_entry' => 5,
            'result_de_points' => 10,
            'result_podium_points' => 1,
            'result_total_points' => 15,
            'result_in_ranking' => 'Y'
        ])->save();

        Model::create([
            'result_id' => self::WSCAT5,
            'result_competition' => Competition::WSCAT1,
            'result_fencer' => Fencer::WCAT5,
            'result_place' => 5,
            'result_points' => 1,
            'result_entry' => 5,
            'result_de_points' => 0,
            'result_podium_points' => 0,
            'result_total_points' => 1,
            'result_in_ranking' => 'Y'
        ])->save();
    }
}
