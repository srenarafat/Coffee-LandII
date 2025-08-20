<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCategoryDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_drinks_and_food_dropdowns_are_displayed(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $drinks = Category::create(['name' => 'Drinks', 'is_active' => true]);
        Category::create(['name' => 'Tea', 'parent_id' => $drinks->id, 'is_active' => true]);
        $food = Category::create(['name' => 'Food', 'is_active' => true]);
        Category::create(['name' => 'Snack', 'parent_id' => $food->id, 'is_active' => true]);
        Category::create(['name' => 'Other', 'is_active' => true]);

        $response = $this->actingAs($cashier)->get('/cashier/pos');
        $response->assertOk();
        $response->assertSee('Drinks');
        $response->assertSee('Food');
        $response->assertDontSee('Other');
    }

    public function test_selecting_subcategory_filters_products(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $drinks = Category::create(['name' => 'Drinks', 'is_active' => true]);
        $tea = Category::create(['name' => 'Tea', 'parent_id' => $drinks->id, 'is_active' => true]);
        $food = Category::create(['name' => 'Food', 'is_active' => true]);
        $snack = Category::create(['name' => 'Snack', 'parent_id' => $food->id, 'is_active' => true]);

        $drinkProduct = Product::create([
            'name' => 'Green Tea',
            'price' => 5,
            'category_id' => $tea->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);
        $foodProduct = Product::create([
            'name' => 'Cookie',
            'price' => 3,
            'category_id' => $snack->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/cashier/pos?category=' . $tea->id);
        $response->assertOk();
        $response->assertSee($drinkProduct->name);
        $response->assertDontSee($foodProduct->name);
    }
    
    public function test_selecting_parent_category_filters_all_descendants(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $drinks = Category::create(['name' => 'Drinks', 'is_active' => true]);
        $tea = Category::create(['name' => 'Tea', 'parent_id' => $drinks->id, 'is_active' => true]);
        $coffee = Category::create(['name' => 'Coffee', 'parent_id' => $drinks->id, 'is_active' => true]);
        $food = Category::create(['name' => 'Food', 'is_active' => true]);
        $snack = Category::create(['name' => 'Snack', 'parent_id' => $food->id, 'is_active' => true]);

        $teaProduct = Product::create([
            'name' => 'Green Tea',
            'price' => 5,
            'category_id' => $tea->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);
        $coffeeProduct = Product::create([
            'name' => 'Latte',
            'price' => 4,
            'category_id' => $coffee->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);
        $snackProduct = Product::create([
            'name' => 'Cookie',
            'price' => 3,
            'category_id' => $snack->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/cashier/pos?category=' . $drinks->id);
        $response->assertOk();
        $response->assertSee($teaProduct->name);
        $response->assertSee($coffeeProduct->name);
        $response->assertDontSee($snackProduct->name);
    }
    
    public function test_dropdown_displays_correct_depth_classes(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $drinks = Category::create(['name' => 'Drinks', 'is_active' => true]);
        $hot = Category::create(['name' => 'Hot', 'parent_id' => $drinks->id, 'is_active' => true]);
        $tea = Category::create(['name' => 'Tea', 'parent_id' => $hot->id, 'is_active' => true]);
        Category::create(['name' => 'Green', 'parent_id' => $tea->id, 'is_active' => true]);

        $response = $this->actingAs($cashier)->get('/cashier/pos');
        $response->assertOk();

        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/dd-depth-1[^>]*>\s*Hot/', $html);
        $this->assertMatchesRegularExpression('/dd-depth-2[^>]*>\s*Tea/', $html);
        $this->assertMatchesRegularExpression('/dd-depth-3[^>]*>\s*Green/', $html);
    }
}