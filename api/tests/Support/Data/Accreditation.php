<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Accreditation as Model;
use Carbon\Carbon;

class Accreditation extends Fixture
{
    public const MFCAT1 = 1;
    public const WFCAT1 = 2;
    public const HOD = 3;
    public const REFEREE = 4;
    public const DIRECTOR = 5;

    protected static function boot()
    {
        Fencer::create();
        Event::create();
        AccreditationTemplate::create();
        self::booted();

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
            'id' => self::HOD,
            'fencer_id' => Fencer::MCAT1,
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
            'fencer_id' => Fencer::WCAT1,
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
            'id' => self::DIRECTOR,
            'fencer_id' => Fencer::WCAT1,
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
