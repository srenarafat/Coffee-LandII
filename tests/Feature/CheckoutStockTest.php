<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_fails_when_stock_is_insufficient()
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
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0, 'exchange_rate' => 4000]);

        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 3,
            ],
        ];

        // Reduce stock below cart quantity
        $product->update(['stock' => 2]);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 3,
            ]);

        $response->assertSessionHas('error', __('messages.stock_not_enough'));
        $this->assertDatabaseMissing('sale_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_checkout_succeeds_when_stock_is_sufficient()
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
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0, 'exchange_rate' => 4000]);

        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 3,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 3,
            ])
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $this->assertEquals(2, $product->fresh()->stock);
    }
}