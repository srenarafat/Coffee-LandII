<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('name', 'Type of Noodles')->first();

        if (! $category) {
            $category = Category::create(['name' => 'Type of Noodles']);
        }

        Product::create([
            'name' => 'Seafood Noodle',
            'price' => 12.50,
            'category_id' => $category->id,
        ]);

        Product::create([
            'name' => 'Beef Noodle',
            'price' => 10.00,
            'category_id' => $category->id,
        ]);
    }
}