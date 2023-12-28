<?php

namespace Tests\Support\Data;

use App\Models\Accreditation as Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Accreditation extends Fixture
{
    public const MFCAT1 = 1;
    public const MFCAT2 = 2;
    public const WFCAT1 = 3;
    public const TEAM2 = 6;
    public const TEAM3 = 7;
    public const COACH = 11;
    public const HOD = 11;
    public const REFEREE = 20;
    public const VOLUNTEER = 21;
    public const DIRECTOR = 30;

    protected static function wasBooted($cls)
    {
        $count = Model::where('id', '>', 0)->count();
        return $count == 9;
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
            'generated' => null,
            'is_dirty' => null,
            'fe_id' => '1002011'
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
            'generated' => null,
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
            'generated' => null,
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
            'generated' => null,
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
            'generated' => null,
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
            'generated' => null,
            'is_dirty' => null,
            'fe_id' => null
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
            'generated' => null,
            'is_dirty' => null,
            'fe_id' => null
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
            'generated' => null,
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
            'generated' => null,
            'is_dirty' => null,
            'fe_id' => null
        ])->save();
    }
}
