<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting, Sale};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountValidationTest extends TestCase
{
    use RefreshDatabase;

    private function setupCart(): array
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 100,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 5,
        ]);
        Setting::create([
            'shop_name' => 'Shop',
            'currency' => '$',
            'discount_percent' => 0,
            'exchange_rate' => 4000,
        ]);

        $key = md5($product->id . '|medium|||');
        $cart = [
            $key => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ],
        ];

        return [$user, $cart];
    }

    public function test_discount_over_100_is_rejected(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 0,
                'cash_riel' => 0,
                'discount' => 150,
            ]);

        $response->assertSessionHasErrors('discount');
        $this->assertNull(Sale::first());
    }

    public function test_total_never_negative(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 0,
                'cash_riel' => 0,
                'discount' => 100,
            ]);

        $sale = Sale::first();
        $this->assertEquals(0, $sale->total);
        $response->assertRedirect(route('cashier.invoice.print', ['sale' => $sale->id, 'auto' => 1]));
    }
}