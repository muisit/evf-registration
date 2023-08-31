<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Country;
use App\Models\Registrar as Model;
use Carbon\Carbon;

class Registrar extends Fixture
{
    public const REGGEN = 1;
    public const REGGER = 2;

    protected static function boot()
    {
        self::booted();
        Fencer::create();

        Model::create([
            'id' => self::REGGEN,
            'user_id' => WPUser::TESTUSER2,
            'country_id' => null
        ])->save();

        Model::create([
            'id' => self::REGGER,
            'user_id' => WPUser::TESTUSER3,
            'country_id' => Country::GER
        ])->save();
    }
}
