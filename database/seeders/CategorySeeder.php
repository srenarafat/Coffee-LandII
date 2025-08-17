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
        $food = Category::firstOrCreate(['name' => 'Food'], ['parent_id' => null]);
        $drinks = Category::firstOrCreate(['name' => 'Drinks'], ['parent_id' => null]);

        $food->update(['parent_id' => null]);
        $drinks->update(['parent_id' => null]);

        Category::where('name', 'Hot Drinks')->update(['name' => 'Hot']);
        Category::where('name', 'Ice Drinks')->update(['name' => 'Iced']);

        foreach (['Breakfast', 'Lunch', 'Snack'] as $child) {
            Category::firstOrCreate([
                'name' => $child,
                'parent_id' => $food->id,
            ]);
        }

        foreach (['Hot', 'Iced'] as $child) {
            Category::firstOrCreate([
                'name' => $child,
                'parent_id' => $drinks->id,
            ]);
        }
    }
}
