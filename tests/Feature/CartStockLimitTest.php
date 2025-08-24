<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartStockLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_quantity_can_exceed_stock()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 2,
        ]);
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0, 'exchange_rate' => 4000]);

        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/update', [
                'product_id' => $product->id,
                'action' => 'increase',
            ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $this->assertEquals(3, session('cart')[$product->id]['quantity']);
    }
}
