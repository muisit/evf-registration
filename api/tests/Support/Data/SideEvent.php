<?php

namespace Tests\Support\Data;

use DB;
use App\Models\SideEvent as Model;
use Carbon\Carbon;

class SideEvent extends Fixture
{
    public const MFCAT1 = 1;
    public const MFCAT2 = 2;
    public const MFTEAM = 3;
    public const WSCAT1 = 4;
    public const DINATOIRE = 5;
    public const GALA = 6;

    protected static function boot()
    {
        Event::create();
        Competition::create();
        self::booted();

        $opens = Carbon::now()->addDays(11)->toDateString();
        Model::create([
            'id' => self::MFCAT1,
            'event_id' => Event::EVENT1,
            'title' => 'Men\'s Foil Category 1',
            'description' => '',
            'costs' => 0,
            'competition_id' => Competition::MFCAT1,
            'starts' => $opens
        ])->save();

        Model::create([
            'id' => self::MFCAT2,
            'event_id' => Event::EVENT1,
            'title' => 'Men\'s Foil Category 2',
            'description' => '',
            'costs' => 0,
            'competition_id' => Competition::MFCAT2,
            'starts' => $opens
        ])->save();

        Model::create([
            'id' => self::MFTEAM,
            'event_id' => Event::EVENT1,
            'title' => 'Men\'s Foil Team',
            'description' => '',
            'costs' => 0,
            'competition_id' => Competition::MFTEAM,
            'starts' => $opens
        ])->save();

        Model::create([
            'id' => self::WSCAT1,
            'event_id' => Event::EVENT1,
            'title' => 'Women\'s Sabre Category 1',
            'description' => '',
            'costs' => 0,
            'competition_id' => Competition::WSCAT1,
            'starts' => $opens
        ])->save();

        Model::create([
            'id' => self::DINATOIRE,
            'event_id' => Event::EVENT1,
            'title' => 'Cocktail Dinatoire',
            'description' => 'Cocktail party, free entry but please register',
            'costs' => 0,
            'competition_id' => null,
            'starts' => $opens
        ])->save();

        Model::create([
            'id' => self::GALA,
            'event_id' => Event::EVENT1,
            'title' => 'Gala Diner',
            'description' => 'Gala Diner on saturday',
            'costs' => 50,
            'competition_id' => null,
            'starts' => $opens
        ])->save();
    }
}
