<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $food = Category::create(['name' => 'Food']);
        $breakfast = Category::create([
            'name' => 'Breakfast',
            'parent_id' => $food->id,
        ]);

        Category::create([
            'name' => 'Type of Noodles',
            'parent_id' => $breakfast->id,
        ]);

        $drinks = Category::create(['name' => 'Drinks']);
        Category::create([
            'name' => 'Coffee',
            'parent_id' => $drinks->id,
        ]);
    }
}
