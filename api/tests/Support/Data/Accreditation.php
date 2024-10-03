<?php

namespace Tests\Support\Data;

use App\Models\Accreditation as Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Accreditation extends Fixture
{
    public const MFCAT1 = 1311;
    public const MFCAT2 = 2231;
    public const WFCAT1 = 3331;
    public const TEAM2 = 6341;
    public const TEAM3 = 7344;
    public const COACH = 11113;
    public const HOD = 114432;
    public const REFEREE = 202133;
    public const VOLUNTEER = 213111;
    public const DIRECTOR = 303112;

    protected static function wasBooted($cls)
    {
        $count = Model::where('id', '>', 0)->count();
        return $count == 10;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        Fencer::create();
        Event::create();
        AccreditationTemplate::create();
        Registration::create();
        self::booted();

        // remove empty accreditations as result of entering registrations
        self::clear();

        // has a registration for both MFCAT1 and MFTEAM
        Model::create([
            'id' => self::MFCAT1,
            'fencer_id' => Fencer::MCAT1,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ATHLETE,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => '1270578'
        ])->save();

        // has a registration for MFCAT2 and MFTEAM
        Model::create([
            'id' => self::MFCAT2,
            'fencer_id' => Fencer::MCAT2,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ATHLETE,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();

        Model::create([
            'id' => self::WFCAT1,
            'fencer_id' => Fencer::WCAT1,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ATHLETE,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();

        Model::create([
            'id' => self::TEAM2,
            'fencer_id' => Fencer::MCAT1B,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ATHLETE,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();

        Model::create([
            'id' => self::TEAM3,
            'fencer_id' => Fencer::MCAT1C,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ATHLETE,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();

        Model::create([
            'id' => self::COACH,
            'fencer_id' => Fencer::MCAT5,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::COUNTRY,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();

        Model::create([
            'id' => self::HOD,
            'fencer_id' => Fencer::WCAT5,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::COUNTRY,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => '1954492'
        ])->save();

        Model::create([
            'id' => self::VOLUNTEER,
            'fencer_id' => Fencer::MCAT5,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ORG,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => '1954492'
        ])->save();

        Model::create([
            'id' => self::REFEREE,
            'fencer_id' => Fencer::MCAT5,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::REFEREE,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();

        Model::create([
            'id' => self::DIRECTOR,
            'fencer_id' => Fencer::MCAT4,
            'event_id' => Event::EVENT1,
            'template_id' => AccreditationTemplate::ORG,
            'data' => '{}',
            'hash' => null,
            'file_hash' => null,
            'file_id' => null,
            'generated' => '2020-01-01',
            'is_dirty' => null,
            'fe_id' => null
        ])->save();
    }
}
