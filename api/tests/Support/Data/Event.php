<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Country;
use App\Models\Event as Model;
use App\Models\EventType;
use Carbon\Carbon;

class Event extends Fixture
{
    public const EVENT1 = 1;
    public const NOSUCHEVENT = 992;

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
        self::booted();
        $regopens = Carbon::now()->subDays(10)->toDateString();
        $regcloses = Carbon::now()->addDays(10)->toDateString();
        $opens = Carbon::now()->addDays(30)->toDateString();
        $year = intval(Carbon::now()->addDays(30)->format('Y'));
        Model::create([
            'event_id' => self::EVENT1,
            'event_name' => 'EVF Individual Championships',
            'event_open' => $opens,
            'event_year' => $year,
            'event_duration' => 4,
            'event_email' => 'test@example.com',
            'event_web' => null,
            'event_location' => 'Somewhere',
            'event_country' => Country::GER,
            'event_type' => EventType::INDIVIDUAL,
            'event_currency_symbol' => '€',
            'event_currency_name' => 'EUR',
            'event_bank' => 'ING',
            'event_account_name' => 'My Name',
            'event_organisers_address' => '',
            'event_iban' => 'DE09DEU0223 0022 0221 02',
            'event_swift' => 'ING/DEUT',
            'event_reference' => 'my holiday in spain',
            'event_in_ranking' => 'Y',
            'event_frontend' => null,
            'event_factor' => 1.2,
            'event_registration_open' => $regopens,
            'event_registration_close' => $regcloses,
            'event_base_fee' => 100,
            'event_competition_fee' => 10,
            'event_payments' => 'group',
            'event_feed' => null,
            'event_config' => '{"use_registration":true,"use_accreditation":true}'
        ])->save();
    }
}
