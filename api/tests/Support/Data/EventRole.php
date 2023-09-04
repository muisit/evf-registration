<?php

namespace Tests\Support\Data;

use DB;
use App\Models\EventRole as Model;

class EventRole extends Fixture
{
    protected static function boot()
    {
        Event::create();
        WPUser::create();
        self::booted();

        Model::create([
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSER2,
            'role_type' => 'organiser'
        ]);
        Model::create([
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSER3,
            'role_type' => 'cashier'
        ]);
        Model::create([
            'event_id' => Event::EVENT1,
            'user_id' => WPUser::TESTUSER4,
            'role_type' => 'accreditation'
        ]);
    }
}
