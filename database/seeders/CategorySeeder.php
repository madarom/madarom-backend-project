<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insertion des données
        Category::insert([
            ['name' => 'Essential Oil', 'name_fr' => 'Huiles essentielles','slug' => 'essential-oil'],
            ['name' => 'Spices', 'name_fr' => 'Épices', 'slug' => 'spices'],
        ]);
    }
}
