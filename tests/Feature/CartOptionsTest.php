<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting, Sale};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setupShop(array $overrides = []): array
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create(array_merge([
            'name'        => 'P',
            'price'       => 2,
            'category_id' => $category->id,
            'shop_id'     => $shop->id,
            'stock'       => 10,
        ], $overrides));
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
    
    public function test_size_prices_are_used()
    {
        [$user, $product] = $this->setupShop([
            'price_small' => 1,
            'price_medium'=> 2,
            'price_large' => 3,
        ]);

        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'size'       => 'small',
        ]);
        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'size'       => 'medium',
        ]);
        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'size'       => 'large',
        ]);

        $cart = session('cart');
        $prices = collect($cart)->pluck('price', 'size');
        $this->assertEquals(1, $prices['small']);
        $this->assertEquals(2, $prices['medium']);
        $this->assertEquals(3, $prices['large']);

        $this->post('/cashier/pos/checkout', [
            'method' => 'cash',
            'cash_usd' => 10,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'price'      => 1,
            'size'       => 'small',
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'price'      => 2,
            'size'       => 'medium',
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'price'      => 3,
            'size'       => 'large',
        ]);
    }

    public function test_missing_price_falls_back_to_medium()
    {
        [$user, $product] = $this->setupShop([
            'price_medium' => 2,
            'price_large'  => 3,
        ]);
        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'size'       => 'small',
        ]);

        $cart = session('cart');
        $item = collect($cart)->first();
        $this->assertEquals(2, $item['price']);
    }

    public function test_default_medium_price_when_size_not_given()
    {
        [$user, $product] = $this->setupShop([
            'price_small'  => 1,
            'price_medium' => 2,
            'price_large'  => 3,
        ]);
        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $cart = session('cart');
        $item = collect($cart)->first();
        $this->assertEquals(2, $item['price']);
    }
    
    public function test_cart_and_invoice_show_medium_size_by_default()
    {
        [$user, $product] = $this->setupShop();

        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $cart = session('cart');
        $item = collect($cart)->first();
        $label = ucfirst($item['size'] ?: 'medium') . ' Size';
        $this->assertEquals('Medium Size', $label);

        $this->post('/cashier/pos/checkout', [
            'method'   => 'cash',
            'cash_usd' => 2,
        ]);

        $sale = Sale::first();
        $response = $this->actingAs($user)->get("/cashier/invoice/{$sale->id}/print");
        $response->assertSee('Medium Size');
    }

    public function test_cart_and_invoice_show_medium_size_when_selected()
    {
        [$user, $product] = $this->setupShop();

        $this->actingAs($user);

        $this->post('/cashier/pos/add', [
            'product_id' => $product->id,
            'quantity'   => 1,
            'size'       => 'medium',
        ]);

        $cart = session('cart');
        $item = collect($cart)->first();
        $label = ucfirst($item['size'] ?: 'medium') . ' Size';
        $this->assertEquals('Medium Size', $label);

        $this->post('/cashier/pos/checkout', [
            'method'   => 'cash',
            'cash_usd' => 2,
        ]);

        $sale = Sale::first();
        $response = $this->actingAs($user)->get("/cashier/invoice/{$sale->id}/print");
        $response->assertSee('Medium Size');
    }
}
