<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SeedCategories::class,
            SeedCountries::class,
            SeedRoleTypes::class,
            SeedRoles::class,
            SeedWeapons::class,
            SeedEventTypes::class,
        ]);
    }
}
