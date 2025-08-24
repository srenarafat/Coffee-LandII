<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartMinimumQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_decrease_at_minimum_keeps_item()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 5,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);
        
        $key  = $this->cartKey($product->id);
        $cart = [
            $key => [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => 1,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/update', [
                'cart_key' => $key,
                'action' => 'decrease',
            ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $this->assertArrayHasKey($key, session('cart'));
        $this->assertEquals(1, session('cart')[$key]['quantity']);
        $response->assertJson([
            'ok' => true,
            'item' => [
                'quantity' => 1,
                'line_total' => $product->price,
            ],
            'totals' => [
                'grand_total' => $product->price,
                'total_items' => 1,
            ],
        ]);
    }
}