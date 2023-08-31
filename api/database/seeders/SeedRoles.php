<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class SeedRoles extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('TD_Role')->insert([
            [
                'role_id' => Role::HOD,
                'role_name' => 'Head of Delegation',
                'role_type' => 1
            ],
            [
                'role_id' => Role::COACH,
                'role_name' => 'Coach',
                'role_type' => 1
            ],
            [
                'role_id' => 5,
                'role_name' => 'Physio',
                'role_type' => 1
            ],
            [
                'role_id' => 6,
                'role_name' => 'Team Support',
                'role_type' => 1
            ],
            [
                'role_id' => Role::REFEREE,
                'role_name' => 'Referee',
                'role_type' => 2
            ],
            [
                'role_id' => 8,
                'role_name' => 'Weapon Control',
                'role_type' => 2
            ],
            [
                'role_id' => 9,
                'role_name' => 'Medical',
                'role_type' => 2
            ],
            [
                'role_id' => 10,
                'role_name' => 'Official',
                'role_type' => 2
            ],
            [
                'role_id' => Role::VOLUNTEER,
                'role_name' => 'Volunteer',
                'role_type' => 2
            ],
            [
                'role_id' => 12,
                'role_name' => 'VIP',
                'role_type' => 2
            ],
            [
                'role_id' => 13,
                'role_name' => 'Media',
                'role_type' => 2
            ],
            [
                'role_id' => Role::DIRECTOR,
                'role_name' => 'EVFC Director',
                'role_type' => 3
            ],
            [
                'role_id' => 16,
                'role_name' => 'Event Manager',
                'role_type' => 2
            ],
            [
                'role_id' => 17,
                'role_name' => 'Referee Co-ordinator',
                'role_type' => 2
            ],
            [
                'role_id' => Role::DT,
                'role_name' => 'DT',
                'role_type' => 2
            ],
            [
                'role_id' => 19,
                'role_name' => 'Team Armourer',
                'role_type' => 1
            ],
            [
                'role_id' => 20,
                'role_name' => 'EVF Member of Honour',
                'role_type' => 3
            ],
            [
                'role_id' => 21,
                'role_name' => 'Cashier',
                'role_type' => 2
            ],
            [
                'role_id' => 22,
                'role_name' => 'EVF Support',
                'role_type' => 3
            ],
            [
                'role_id' => 23,
                'role_name' => 'Logistics',
                'role_type' => 2
            ],
            [
                'role_id' => 24,
                'role_name' => 'Tech Support',
                'role_type' => 2
            ],
        ]);
    }
}
