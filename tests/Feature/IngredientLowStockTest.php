<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Ingredient, Setting};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientLowStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_low_stock_count_uses_ingredients_only()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);
        Setting::create(['low_stock_threshold' => 5]);

        $category = Category::create(['name' => 'C']);
        Product::create([
            'name' => 'LowProduct',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 1,
        ]);

        Ingredient::create(['name' => 'Sugar', 'unit' => 'g', 'stock' => 3]);
        Ingredient::create(['name' => 'Salt', 'unit' => 'g', 'stock' => 10]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertViewHas('lowStockCount', 1);
    }

    public function test_low_stock_route_lists_low_ingredients()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);
        Setting::create(['low_stock_threshold' => 5]);

        $category = Category::create(['name' => 'C']);
        Product::create([
            'name' => 'LowProduct',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 1,
        ]);

        Ingredient::create(['name' => 'Sugar', 'unit' => 'g', 'stock' => 3]);
        Ingredient::create(['name' => 'Salt', 'unit' => 'g', 'stock' => 10]);

        $response = $this->actingAs($user)->get(route('admin.ingredient-stock.low'));
        $response->assertOk();
        $response->assertSee('Sugar');
        $response->assertDontSee('LowProduct');
    }
}
