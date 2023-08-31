<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedCountries extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('TD_Country')->insert([
            [
                'country_id' => 1,
                'country_abbr' => 'GBR',
                'country_name' => 'Great Britain',
                'country_flag_path' => 'wp-content/uploads/2021/07/gb.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 2,
                'country_abbr' => 'ITA',
                'country_name' => 'Italy',
                'country_flag_path' => 'wp-content/uploads/2021/07/it.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 3,
                'country_abbr' => 'CRO',
                'country_name' => 'Croatia',
                'country_flag_path' => 'wp-content/uploads/2021/07/hr.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 4,
                'country_abbr' => 'SWE',
                'country_name' => 'Sweden',
                'country_flag_path' => 'wp-content/uploads/2021/07/se.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 5,
                'country_abbr' => 'AUT',
                'country_name' => 'Austria',
                'country_flag_path' => 'wp-content/uploads/2021/07/at.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 6,
                'country_abbr' => 'BLR',
                'country_name' => 'Belarus',
                'country_flag_path' => 'wp-content/uploads/2021/07/by.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 7,
                'country_abbr' => 'BEL',
                'country_name' => 'Belgium',
                'country_flag_path' => 'wp-content/uploads/2021/07/be.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 8,
                'country_abbr' => 'CZE',
                'country_name' => "Czech Republic",
                'country_flag_path' => 'wp-content/uploads/2021/07/cz.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 9,
                'country_abbr' => 'DEN',
                'country_name' => 'Denmark',
                'country_flag_path' => 'wp-content/uploads/2021/07/dk.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 10,
                'country_abbr' => 'FIN',
                'country_name' => 'Finland',
                'country_flag_path' => 'wp-content/uploads/2021/07/fi.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 11,
                'country_abbr' => 'FRA',
                'country_name' => 'France',
                'country_flag_path' => 'wp-content/uploads/2021/07/fr.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 12,
                'country_abbr' => 'GER',
                'country_name' => 'Germany',
                'country_flag_path' => 'wp-content/uploads/2021/07/de.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 13,
                'country_abbr' => 'HUN',
                'country_name' => 'Hungary',
                'country_flag_path' => 'wp-content/uploads/2021/07/hu.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 14,
                'country_abbr' => 'IRL',
                'country_name' => 'Ireland',
                'country_flag_path' => 'wp-content/uploads/2021/07/ie.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 15,
                'country_abbr' => 'ISL',
                'country_name' => 'Iceland',
                'country_flag_path' => 'wp-content/uploads/2021/07/is.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 16,
                'country_abbr' => 'ISR',
                'country_name' => 'Israel',
                'country_flag_path' => 'wp-content/uploads/2021/07/il.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 17,
                'country_abbr' => 'LAT',
                'country_name' => 'Latvia',
                'country_flag_path' => 'wp-content/uploads/2021/07/lv.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 18,
                'country_abbr' => 'LTU',
                'country_name' => "Lithuania",
                'country_flag_path' => 'wp-content/uploads/2021/07/lt.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 19,
                'country_abbr' => 'MKD',
                'country_name' => 'Macedonia',
                'country_flag_path' => 'wp-content/uploads/2021/07/mk.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 20,
                'country_abbr' => 'MON',
                'country_name' => 'Monaco',
                'country_flag_path' => 'wp-content/uploads/2021/07/mc.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 21,
                'country_abbr' => 'NED',
                'country_name' => 'Netherlands',
                'country_flag_path' => 'wp-content/uploads/2021/07/nl.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 22,
                'country_abbr' => 'NOR',
                'country_name' => 'Norway',
                'country_flag_path' => 'wp-content/uploads/2021/07/no.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 23,
                'country_abbr' => 'POL',
                'country_name' => 'Poland',
                'country_flag_path' => 'wp-content/uploads/2021/07/pl.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 24,
                'country_abbr' => 'POR',
                'country_name' => 'Portugal',
                'country_flag_path' => 'wp-content/uploads/2021/07/pt.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 25,
                'country_abbr' => 'ROU',
                'country_name' => 'Romania',
                'country_flag_path' => 'wp-content/uploads/2021/07/ro.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 26,
                'country_abbr' => 'RUS',
                'country_name' => 'Russia',
                'country_flag_path' => 'wp-content/uploads/2021/07/ru.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 27,
                'country_abbr' => 'SRB',
                'country_name' => 'Serbia',
                'country_flag_path' => 'wp-content/uploads/2021/07/rs.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 28,
                'country_abbr' => 'SVK',
                'country_name' => 'Slovakia',
                'country_flag_path' => 'wp-content/uploads/2021/07/sk.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 29,
                'country_abbr' => 'SUI',
                'country_name' => 'Switzerland',
                'country_flag_path' => 'wp-content/uploads/2021/07/ch.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 31,
                'country_abbr' => 'ESP',
                'country_name' => 'Spain',
                'country_flag_path' => 'wp-content/uploads/2021/07/es.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 32,
                'country_abbr' => 'UKR',
                'country_name' => 'Ukraine',
                'country_flag_path' => 'wp-content/uploads/2021/07/ua.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 33,
                'country_abbr' => 'EST',
                'country_name' => 'Estonia',
                'country_flag_path' => 'wp-content/uploads/2021/07/ee.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 34,
                'country_abbr' => 'GEO',
                'country_name' => 'Georgia',
                'country_flag_path' => 'wp-content/uploads/2021/07/ge.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 35,
                'country_abbr' => 'GRE',
                'country_name' => 'Greece',
                'country_flag_path' => 'wp-content/uploads/2021/07/gr.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 36,
                'country_abbr' => 'LUX',
                'country_name' => 'Luxemburg',
                'country_flag_path' => 'wp-content/uploads/2021/07/lu.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 37,
                'country_abbr' => 'RSM',
                'country_name' => 'Republic of San Marino',
                'country_flag_path' => 'wp-content/uploads/2021/07/sm.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 38,
                'country_abbr' => 'TUR',
                'country_name' => 'Turkey',
                'country_flag_path' => 'wp-content/uploads/2021/07/tr.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 39,
                'country_abbr' => 'SLO',
                'country_name' => 'Slovenia',
                'country_flag_path' => 'wp-content/uploads/2021/07/si.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 40,
                'country_abbr' => 'BUL',
                'country_name' => 'Bulgaria',
                'country_flag_path' => 'wp-content/uploads/2021/07/bg.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 41,
                'country_abbr' => 'ORG',
                'country_name' => 'Organisers',
                'country_flag_path' => 'wp-content/uploads/2021/07/eu.png',
                'country_registered' => 'N'
            ],
            [
                'country_id' => 42,
                'country_abbr' => 'AZE',
                'country_name' => 'Azerbaijan',
                'country_flag_path' => 'wp-content/uploads/2021/07/az.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 43,
                'country_abbr' => 'MAL',
                'country_name' => 'Malta',
                'country_flag_path' => 'wp-content/uploads/2021/07/mt.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 44,
                'country_abbr' => 'ARM',
                'country_name' => 'Armenia',
                'country_flag_path' => 'wp-content/uploads/2021/07/am.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 45,
                'country_abbr' => 'BIH',
                'country_name' => 'Bosnia and Herzogovena',
                'country_flag_path' => 'wp-content/uploads/2021/07/ba.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 46,
                'country_abbr' => 'TST',
                'country_name' => 'Testonia',
                'country_flag_path' => 'wp-content/uploads/2021/07/eu.png',
                'country_registered' => 'N'
            ],
            [
                'country_id' => 47,
                'country_abbr' => 'ALB',
                'country_name' => 'Albania',
                'country_flag_path' => 'wp-content/uploads/2021/07/al.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 48,
                'country_abbr' => 'AND',
                'country_name' => 'Andorra',
                'country_flag_path' => 'wp-content/uploads/2021/07/ad.png',
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 49,
                'country_abbr' => 'OTH',
                'country_name' => 'Other',
                'country_flag_path' => null,
                'country_registered' => 'Y'
            ],
            [
                'country_id' => 50,
                'country_abbr' => 'MDA',
                'country_name' => 'Moldova',
                'country_flag_path' => null,
                'country_registered' => 'Y'
            ],
        ]);
    }
}
