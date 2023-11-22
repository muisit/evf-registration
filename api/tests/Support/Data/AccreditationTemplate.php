<?php

namespace Tests\Support\Data;

use DB;
use App\Models\AccreditationTemplate as Model;
use Carbon\Carbon;

class AccreditationTemplate extends Fixture
{
    public const ATHLETE = 1;
    public const ORG = 2;

    protected static function boot()
    {
        Event::create();
        self::booted();

        Model::create([
            'id' => self::ATHLETE,
            'name' => 'Athlete',
            'content' => '{}',
            'event_id' => Event::EVENT1,
        ])->save();

        Model::create([
            'id' => self::ORG,
            'name' => 'Athlete',
            'content' => '{}',
            'event_id' => Event::EVENT1,
        ])->save();
    }
}
