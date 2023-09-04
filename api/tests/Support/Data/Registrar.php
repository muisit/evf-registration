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
        WPUser::create();

        Model::create([
            'id' => self::REGGEN,
            'user_id' => WPUser::TESTUSERGENHOD,
            'country_id' => null
        ])->save();

        Model::create([
            'id' => self::REGGER,
            'user_id' => WPUser::TESTUSERHOD,
            'country_id' => Country::GER
        ])->save();
    }
}
