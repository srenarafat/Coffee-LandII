<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting, Sale};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLimitTest extends TestCase
{
    use RefreshDatabase;

    private function setupCart(float $price = 1, int $quantity = 1): array
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => $price,
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
                'quantity' => $quantity,
            ],
        ];

        return [$user, $cart];
    }

    private function dynamicMax(array $cart): int
    {
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        return (int) ceil($subtotal / 100) * 100;
    }

    public function test_checkout_fails_when_cash_usd_exceeds_limit(): void
    {
        [$user, $cart] = $this->setupCart();
        $limit = $this->dynamicMax($cart);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 150,
                'cash_riel' => 0,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded', ['limit' => $limit]));
        $response->assertSessionHasErrors('cash_usd');
    }

    public function test_checkout_fails_when_cash_riel_exceeds_limit(): void
    {
        [$user, $cart] = $this->setupCart();
        $limit = $this->dynamicMax($cart);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 0,
                'cash_riel' => 500000,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded', ['limit' => $limit]));
        $response->assertSessionHasErrors('cash_riel');
    }

    public function test_checkout_rejects_non_numeric_values(): void
    {
        [$user, $cart] = $this->setupCart();
        $limit = $this->dynamicMax($cart);


        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 'abc',
                'cash_riel' => 'xyz',
                'discount' => 0,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded', ['limit' => $limit]));
        $response->assertSessionHasErrors(['cash_usd', 'cash_riel']);
    }

    public function test_checkout_rejects_negative_values(): void
    {
        [$user, $cart] = $this->setupCart();
        $limit = $this->dynamicMax($cart);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => -1,
                'cash_riel' => -100,
                'discount' => 0,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded', ['limit' => $limit]));
        $response->assertSessionHasErrors(['cash_usd', 'cash_riel']);
    }

    public function test_checkout_accepts_valid_numeric_values(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 5,
                'cash_riel' => 0,
                'discount' => 0,
            ]);

        $sale = Sale::first();
        $response->assertRedirect(route('cashier.invoice.print', ['sale' => $sale->id, 'auto' => 1]));
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
    }

    /**
     * @dataProvider cartTotalsProvider
     */
    public function test_checkout_enforces_dynamic_payment_limit(float $subtotal): void
    {
        [$user, $cart] = $this->setupCart($subtotal);
        $dynamicMax = $this->dynamicMax($cart);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => $dynamicMax + 50,
                'cash_riel' => 0,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded', ['limit' => $dynamicMax]));
        $response->assertSessionHasErrors('cash_usd');

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => $dynamicMax,
                'cash_riel' => 0,
            ]);

        $sale = Sale::first();
        $response->assertRedirect(route('cashier.invoice.print', ['sale' => $sale->id, 'auto' => 1]));
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
    }

    public static function cartTotalsProvider(): array
    {
        return [
            [150],
            [250],
            [950],
        ];
    }
}