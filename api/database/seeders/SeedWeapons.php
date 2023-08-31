<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Weapon;

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
                'weapon_id' => Weapon::MF,
                'weapon_abbr' => 'MF',
                'weapon_name' => 'Mens Foil',
                'weapon_gender' => 'M'
            ],
            [
                'weapon_id' => Weapon::ME,
                'weapon_abbr' => 'ME',
                'weapon_name' => 'Mens Epee',
                'weapon_gender' => 'M'
            ],
            [
                'weapon_id' => Weapon::MS,
                'weapon_abbr' => 'MS',
                'weapon_name' => 'Mens Sabre',
                'weapon_gender' => 'M'
            ],
            [
                'weapon_id' => Weapon::WF,
                'weapon_abbr' => 'WF',
                'weapon_name' => 'Womens Foil',
                'weapon_gender' => 'F'
            ],
            [
                'weapon_id' => Weapon::WE,
                'weapon_abbr' => 'WE',
                'weapon_name' => 'Womens Epee',
                'weapon_gender' => 'F'
            ],
            [
                'weapon_id' => Weapon::WS,
                'weapon_abbr' => 'WS',
                'weapon_name' => 'Womens Sabre',
                'weapon_gender' => 'F'
            ],
        ]);
    }
}
