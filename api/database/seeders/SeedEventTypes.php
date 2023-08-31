<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EventType;

class SeedEventTypes extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('TD_Event_Type')->insert([
            [
                'event_type_id' => EventType::INDIVIDUAL,
                'event_type_abbr' => 'E',
                'event_type_name' => 'European Individual',
                'event_type_group' => 'Fencer'
            ],
            [
                'event_type_id' => EventType::WORLD,
                'event_type_abbr' => 'W',
                'event_type_name' => 'World',
                'event_type_group' => 'Fencer'
            ],
            [
                'event_type_id' => EventType::TEAM,
                'event_type_abbr' => 'ET',
                'event_type_name' => 'Team Championships',
                'event_type_group' => 'Team'
            ],
            [
                'event_type_id' => EventType::CIRCUIT,
                'event_type_abbr' => 'C',
                'event_type_name' => 'Circuit',
                'event_type_group' => 'Individual'
            ],
        ]);
    }
}
