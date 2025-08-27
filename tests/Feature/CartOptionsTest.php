<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setupShop(): array
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
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0, 'exchange_rate' => 4000]);

        return [$user, $product];
    }

    public function test_identical_options_increment_quantity()
    {
        [$user, $product] = $this->setupShop();

        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'M',
            'sugar_level' => 50,
            'ice_option' => 'normal',
            'note' => 'hello',
        ]);
        $key = array_key_first(session('cart'));

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'M',
            'sugar_level' => 50,
            'ice_option' => 'normal',
            'note' => 'hello',
        ]);

        $cart = session('cart');
        $this->assertCount(1, $cart);
        $this->assertEquals(2, $cart[$key]['quantity']);
        $this->assertEquals('M', $cart[$key]['size']);
        $this->assertEquals(50, $cart[$key]['sugar_level']);
        $this->assertEquals('normal', $cart[$key]['ice_option']);
        $this->assertEquals('hello', $cart[$key]['note'] ?? null);

        $this->post('/cashier/pos/checkout', [
            'method' => 'cash',
            'cash_usd' => 4,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'size' => 'M',
            'sugar_level' => 50,
            'ice_option' => 'normal',
            'note' => 'hello',
        ]);
    }

    public function test_different_options_create_separate_lines()
    {
        [$user, $product] = $this->setupShop();

        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'S',
            'sugar_level' => 0,
            'ice_option' => 'less',
            'note' => 'a',
        ]);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'L',
            'sugar_level' => 0,
            'ice_option' => 'less',
            'note' => 'a',
        ]);

        $cart = session('cart');
        $this->assertCount(2, $cart);
        $keys = array_keys($cart);
        $this->assertEquals('S', $cart[$keys[0]]['size']);
        $this->assertEquals('L', $cart[$keys[1]]['size']);

        $this->post('/cashier/pos/checkout', [
            'method' => 'cash',
            'cash_usd' => 2,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'S',
            'sugar_level' => 0,
            'ice_option' => 'less',
            'note' => 'a',
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'L',
            'sugar_level' => 0,
            'ice_option' => 'less',
            'note' => 'a',
        ]);
    }

    public function test_more_sweet_option_accepts_150()
    {
        [$user, $product] = $this->setupShop();

        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'M',
            'sugar_level' => 150,
            'ice_option' => 'normal',
        ]);

        $cart = session('cart');
        $this->assertCount(1, $cart);
        $key = array_key_first($cart);
        $this->assertEquals(150, $cart[$key]['sugar_level']);

        $this->post('/cashier/pos/checkout', [
            'method' => 'cash',
            'cash_usd' => 4,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'M',
            'sugar_level' => 150,
            'ice_option' => 'normal',
        ]);
    }
}
