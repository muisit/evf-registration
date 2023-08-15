<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedWeapons extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('TD_Weapon')->insert([
            [
                'weapon_id' => 1,
                'weapon_abbr' => 'MF',
                'weapon_name' => 'Mens Foil',
                'weapon_gender' => 'M'
            ],
            [
                'weapon_id' => 2,
                'weapon_abbr' => 'ME',
                'weapon_name' => 'Mens Epee',
                'weapon_gender' => 'M'
            ],
            [
                'weapon_id' => 3,
                'weapon_abbr' => 'MS',
                'weapon_name' => 'Mens Sabre',
                'weapon_gender' => 'M'
            ],
            [
                'weapon_id' => 4,
                'weapon_abbr' => 'WF',
                'weapon_name' => 'Womens Foil',
                'weapon_gender' => 'F'
            ],
            [
                'weapon_id' => 5,
                'weapon_abbr' => 'WE',
                'weapon_name' => 'Womens Epee',
                'weapon_gender' => 'F'
            ],
            [
                'weapon_id' => 6,
                'weapon_abbr' => 'WS',
                'weapon_name' => 'Womens Sabre',
                'weapon_gender' => 'F'
            ],
        ]);
    }
}
