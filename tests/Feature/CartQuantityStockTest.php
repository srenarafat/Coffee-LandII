<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartQuantityStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_ajax_increment_beyond_stock_succeeds()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 5,
        ]);

        $this->actingAs($user);
        $this->post('/cashier/pos/add', ['product_id' => $product->id, 'quantity' => 5]);

        $response = $this->post('/cashier/pos/update', [
            'product_id' => $product->id,
            'action' => 'increase',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(6, session('cart')[$product->id]['quantity']);
    }

    public function test_ajax_increment_beyond_stock_succeeds()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 5,
        ]);

        $this->actingAs($user);
        $this->post('/cashier/pos/add', ['product_id' => $product->id, 'quantity' => 5]);

        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post('/cashier/pos/update', [
                'product_id' => $product->id,
                'action' => 'increase',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(6, session('cart')[$product->id]['quantity']);
    }
}