<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Competition as Model;
use App\Models\Weapon;
use App\Models\Category;
use Carbon\Carbon;

class Competition extends Fixture
{
    public const MFCAT1 = 1;
    public const MFCAT2 = 2;
    public const MFTEAM = 3;
    public const WSCAT1 = 4;

    protected static function wasBooted($cls)
    {
        $count = Model::where('competition_id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        Event::create();
        Fencer::create();
        self::booted();

        $opens = Carbon::now()->addDays(11)->toDateString();
        $check = Carbon::now()->addDays(10)->toDateString();
        Model::create([
            'competition_id' => self::MFCAT1,
            'competition_event' => Event::EVENT1,
            'competition_category' => Category::CAT1,
            'competition_weapon' => Weapon::MF,
            'competition_opens' => $opens,
            'competition_weapon_check' => $check
        ])->save();

        Model::create([
            'competition_id' => self::MFCAT2,
            'competition_event' => Event::EVENT1,
            'competition_category' => Category::CAT2,
            'competition_weapon' => Weapon::MF,
            'competition_opens' => $opens,
            'competition_weapon_check' => $check
        ])->save();

        Model::create([
            'competition_id' => self::MFTEAM,
            'competition_event' => Event::EVENT1,
            'competition_category' => Category::TEAM,
            'competition_weapon' => Weapon::MF,
            'competition_opens' => $opens,
            'competition_weapon_check' => $check
        ])->save();

        Model::create([
            'competition_id' => self::WSCAT1,
            'competition_event' => Event::EVENT1,
            'competition_category' => Category::CAT1,
            'competition_weapon' => Weapon::WS,
            'competition_opens' => $opens,
            'competition_weapon_check' => $check
        ])->save();
    }
}
