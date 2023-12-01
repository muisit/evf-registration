<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class SeedCategories extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('TD_Category')->where('category_id', Category::CAT1)->first();
        if (!empty($data)) return;

        DB::table('TD_Category')->insert([[
            'category_id' => Category::CAT1,
            'category_name' => 'Cat 1',
            'category_type' => 'I',
            'category_abbr' => '1',
            'category_value' => 1
        ],[
            'category_id' => Category::CAT2,
            'category_name' => 'Cat 2',
            'category_type' => 'I',
            'category_abbr' => '2',
            'category_value' => 2
        ],[
            'category_id' => Category::CAT3,
            'category_name' => 'Cat 3',
            'category_type' => 'I',
            'category_abbr' => '3',
            'category_value' => 3
        ],[
            'category_id' => Category::CAT4,
            'category_name' => 'Cat 4',
            'category_type' => 'I',
            'category_abbr' => '4',
            'category_value' => 4
        ],[
            'category_id' => Category::TEAM,
            'category_name' => 'Team',
            'category_type' => 'T',
            'category_abbr' => 'T',
            'category_value' => 0
        ],[
            'category_id' => Category::GVET,
            'category_name' => 'Grand Veterans',
            'category_type' => 'T',
            'category_abbr' => 'T(G)',
            'category_value' => 0
        ],[
            'category_id' => Category::CAT5,
            'category_name' => 'Cat 5',
            'category_type' => 'I',
            'category_abbr' => '5',
            'category_value' => 5
        ]]);
    }
}
