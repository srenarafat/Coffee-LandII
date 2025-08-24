<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_quantity_updates()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 2,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);

        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/update', [
                'product_id' => $product->id,
                'action' => 'set_quantity',
                'quantity' => 4,
            ], ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'item' => [
                    'quantity' => 4,
                    'line_total' => 8,
                ],
                'totals' => [
                    'grand_total' => 8,
                    'total_items' => 4,
                ],
            ]);
    }

    public function test_set_quantity_does_not_enforce_stock_limit()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 3,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 5,
        ]);

        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/update', [
                'product_id' => $product->id,
                'action' => 'set_quantity',
                'quantity' => 10,
            ], ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(200)
            ->assertJson([
                'ok' => true,
                'item' => [
                    'quantity' => 10,
                    'line_total' => 30,
                ],
                'totals' => [
                    'grand_total' => 30,
                    'total_items' => 10,
                ],
            ]);
    }
}