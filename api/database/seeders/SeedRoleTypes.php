<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedRoleTypes extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('TD_Role_Type')->insert([
            [
                'role_type_id' => 1,
                'role_type_name' => 'Federation Non-Fencers',
                'org_declaration' => 'Country'
            ],
            [
                'role_type_id' => 2,
                'role_type_name' => 'Organisers',
                'org_declaration' => 'Org'
            ],
            [
                'role_type_id' => 3,
                'role_type_name' => 'EVF',
                'org_declaration' => 'EVF'
            ],
            [
                'role_type_id' => 4,
                'role_type_name' => 'FIE',
                'org_declaration' => 'FIE'
            ],
        ]);
    }
}
