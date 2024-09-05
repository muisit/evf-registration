<?php

namespace Tests\Support\Data;

use App\Models\AccreditationDocument as Model;
use Illuminate\Support\Facades\DB;

class AccreditationDocument extends Fixture
{
    public const MFCAT1 = 13912;
    public const MFCAT2 = 23912;

    protected static function wasBooted($cls)
    {
        $count = Model::where('id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        Accreditation::create();
        self::booted();

        Model::create([
            'id' => self::MFCAT1,
            'accreditation_id' => Accreditation::MFCAT1,
            'card' => '21',
            'document_nr' => '88',
            'payload' => '{}',
            'status' => Model::STATUS_CREATED,
            'checkin' => '2021-01-01 01:00:00',
            'process_start' => null,
            'process_end' => null,
            'checkout' => null,
            'checkout_badge' => null,
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        Model::create([
            'id' => self::MFCAT2,
            'accreditation_id' => Accreditation::MFCAT2,
            'card' => '25',
            'document_nr' => '8',
            'payload' => '{}',
            'status' => Model::STATUS_CHECKOUT,
            'checkin' => '2021-01-01 01:00:00',
            'process_start' => '2021-01-01 02:00:00',
            'process_end' => '2021-01-01 03:00:00',
            'checkout' => '2021-01-11 01:23:34',
            'checkout_badge' => '1270578',
            'created_at' => '2020-01-01 00:00:01',
            'updated_at' => '2020-01-11 01:23:34'
        ])->save();
    }
}
