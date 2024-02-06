<?php

namespace Tests\Support\Data;

use App\Models\AccreditationUser as Model;
use Illuminate\Support\Facades\DB;

class AccreditationUser extends Fixture
{
    public const ADMIN = 1;
    public const ACCREDITATION = 2;
    public const CHECKIN = 3;
    public const CHECKOUT = 6;
    public const DT = 7;
    public const MFCAT1 = 11;
    public const VOLUNTEER = 21;
    public const NOSUCHID = 40;

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

        // Generic Admin user
        Model::create([
            'id' => self::ADMIN,
            'event_id' => Event::EVENT1,
            'accreditation_id' => null,
            'code' => '99058223000001',
            'type' => 'organiser',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        // Generic Accreditation
        Model::create([
            'id' => self::ACCREDITATION,
            'event_id' => Event::EVENT1,
            'accreditation_id' => null,
            'code' => '99167043180001',
            'type' => 'accreditation',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        // Generic Checkin user
        Model::create([
            'id' => self::CHECKIN,
            'event_id' => Event::EVENT1,
            'accreditation_id' => null,
            'code' => '99212062610001',
            'type' => 'checkin',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        // Generic Checkout User
        Model::create([
            'id' => self::CHECKOUT,
            'event_id' => Event::EVENT1,
            'accreditation_id' => null,
            'code' => '99323934600001',
            'type' => 'checkout',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        // Generic DT user
        Model::create([
            'id' => self::DT,
            'event_id' => Event::EVENT1,
            'accreditation_id' => null,
            'code' => '99441611210001',
            'type' => 'dt',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        // Specific Accreditation User
        Model::create([
            'id' => self::MFCAT1,
            'event_id' => Event::EVENT1,
            'accreditation_id' => Accreditation::MFCAT1,
            'code' => '11127057800000',
            'type' => 'accreditation',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

        // Specific Checkin User
        Model::create([
            'id' => self::VOLUNTEER,
            'event_id' => Event::EVENT1,
            'accreditation_id' => Accreditation::VOLUNTEER,
            'code' => '11195449260000',
            'type' => 'checkin',
            'created_at' => '2020-01-01 01:23:34',
            'updated_at' => '2020-01-01 01:23:34'
        ])->save();

    }
}
