<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodCartTest extends TestCase
{
    use RefreshDatabase;

    private function setupFood(): array
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $food = Category::create(['name' => 'Food', 'shop_id' => $shop->id]);
        $product = Product::create([
            'name'        => 'F',
            'price'       => 3,
            'category_id' => $food->id,
            'shop_id'     => $shop->id,
            'stock'       => 10,
        ]);
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0, 'exchange_rate' => 4000]);

        return [$user, $product];
    }

    public function test_food_product_has_no_size_in_cart_view()
    {
        [$user, $product] = $this->setupFood();
        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $cart = session('cart');
        $this->assertCount(1, $cart);
        $key = array_key_first($cart);
        $this->assertArrayNotHasKey('size', $cart[$key]);

        $html = view('partials.cart', ['routePrefix' => 'cashier'])->render();
        $this->assertStringNotContainsString('Medium Size', $html);
        $this->assertStringContainsString('"size":""', $html);
    }
}
