<?php

namespace Tests\Support\Data;

use DB;
use App\Models\Country;
use App\Models\Fencer as Model;
use Carbon\Carbon;

class Fencer extends Fixture
{
    public const MCAT1 = 1;
    public const MCAT2 = 2;
    public const MCAT3 = 3;
    public const MCAT4 = 4;
    public const MCAT5 = 5;
    public const WCAT1 = 6;
    public const WCAT2 = 7;
    public const WCAT3 = 8;
    public const WCAT4 = 9;
    public const WCAT5 = 10;
    public const MCAT1B = 11;
    public const MCAT1C = 12;
    public const NOSUCHFENCER = 2882;

    protected static function wasBooted($cls)
    {
        $count = Model::where('fencer_id', '>', 0)->count();
        return $count > 0;
    }

    protected static function clear()
    {
        DB::table(Model::tableName())->delete();
    }

    protected static function boot()
    {
        self::booted();
        $cat1 = Carbon::now()->subYears(41);
        $cat2 = Carbon::now()->subYears(51);
        $cat3 = Carbon::now()->subYears(61);
        $cat4 = Carbon::now()->subYears(71);
        $cat5 = Carbon::now()->subYears(81);

        Model::create([
            'fencer_id' => self::MCAT1,
            'fencer_country' => Country::GER,
            'fencer_firstname' => 'Tést',
            'fencer_surname' => 'De La Teste',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat1->toDateString(),
            'fencer_picture' => Model::PICTURE_NONE
        ])->save();

        Model::create([
            'fencer_id' => self::MCAT2,
            'fencer_country' => Country::ITA,
            'fencer_firstname' => 'John',
            'fencer_surname' => 'Testita',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat2->toDateString(),
            'fencer_picture' => Model::PICTURE_UPLOADED
        ])->save();

        Model::create([
            'fencer_id' => self::MCAT3,
            'fencer_country' => Country::FRA,
            'fencer_firstname' => 'Testi',
            'fencer_surname' => 'D\'Teste',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat3->toDateString(),
            'fencer_picture' => Model::PICTURE_ACCEPTED
        ])->save();

        Model::create([
            'fencer_id' => self::MCAT4,
            'fencer_country' => Country::NED,
            'fencer_firstname' => 'Kees',
            'fencer_surname' => 'de Tester',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat4->toDateString(),
            'fencer_picture' => Model::PICTURE_NONE
        ])->save();

        Model::create([
            'fencer_id' => self::MCAT5,
            'fencer_country' => Country::GER,
            'fencer_firstname' => 'Hans',
            'fencer_surname' => 'Versucher',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat5->toDateString(),
            'fencer_picture' => Model::PICTURE_UPLOADED
        ])->save();

        Model::create([
            'fencer_id' => self::WCAT1,
            'fencer_country' => Country::GER,
            'fencer_firstname' => 'Christina',
            'fencer_surname' => 'De La Teste',
            'fencer_gender' => 'F',
            'fencer_dob' => $cat1->toDateString(),
            'fencer_picture' => Model::PICTURE_NONE
        ])->save();

        Model::create([
            'fencer_id' => self::WCAT2,
            'fencer_country' => Country::ITA,
            'fencer_firstname' => 'Joanna',
            'fencer_surname' => 'Testita',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat2->toDateString(),
            'fencer_picture' => Model::PICTURE_UPLOADED
        ])->save();

        Model::create([
            'fencer_id' => self::WCAT3,
            'fencer_country' => Country::FRA,
            'fencer_firstname' => 'Emilie',
            'fencer_surname' => 'D\'Teste',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat3->toDateString(),
            'fencer_picture' => Model::PICTURE_ACCEPTED
        ])->save();

        Model::create([
            'fencer_id' => self::WCAT4,
            'fencer_country' => Country::NED,
            'fencer_firstname' => 'Anne',
            'fencer_surname' => 'de Tester',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat4->toDateString(),
            'fencer_picture' => Model::PICTURE_NONE
        ])->save();

        Model::create([
            'fencer_id' => self::WCAT5,
            'fencer_country' => Country::GER,
            'fencer_firstname' => 'Kathi',
            'fencer_surname' => 'Versucher',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat5->toDateString(),
            'fencer_picture' => Model::PICTURE_UPLOADED
        ])->save();

        Model::create([
            'fencer_id' => self::MCAT1B,
            'fencer_country' => Country::GER,
            'fencer_firstname' => 'Peter',
            'fencer_surname' => 'Versucher',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat1->toDateString(),
            'fencer_picture' => Model::PICTURE_NONE
        ])->save();

        Model::create([
            'fencer_id' => self::MCAT1C,
            'fencer_country' => Country::GER,
            'fencer_firstname' => 'Karl',
            'fencer_surname' => 'Versucher',
            'fencer_gender' => 'M',
            'fencer_dob' => $cat1->toDateString(),
            'fencer_picture' => Model::PICTURE_NONE
        ])->save();
    }
}
