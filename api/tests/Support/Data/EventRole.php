<?php

namespace Tests\Support\Data;

use DB;
use App\Models\EventRole as Model;

class EventRole extends Fixture
{
    public const ORGANISER = 1;
    public const CASHIER = 2;
    public const ACCREDITATION = 3;
    public const ORGANISER2 = 4;
    public const REGISTRAR = 5;
    public const NOSUCHID = 398923;

    protected static function wasBooted($cls)
    {
        $count = Model::where('event_id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        Event::create();
        WPUser::create();
        self::booted();

        Model::create([
            'id' => self::ORGANISER,
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSER2,
            'role_type' => 'organiser'
        ]);
        Model::create([
            'id' => self::CASHIER,
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSER3,
            'role_type' => 'cashier'
        ]);
        Model::create([
            'id' => self::ACCREDITATION,
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSER4,
            'role_type' => 'accreditation'
        ]);
        Model::create([
            'id' => self::ORGANISER2,
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSERORGANISER,
            'role_type' => 'organiser'
        ]);
        Model::create([
            'id' => self::REGISTRAR,
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSERREGISTRAR,
            'role_type' => 'registrar'
        ]);
    }
}
