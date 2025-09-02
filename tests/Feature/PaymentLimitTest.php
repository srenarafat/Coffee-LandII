<?php

namespace Tests\Feature;

use App\Models\{User, Shop, Category, Product, Setting, Sale};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLimitTest extends TestCase
{
    use RefreshDatabase;

    private function setupCart(int $total = 1): array
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => $total,
        ]);
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0, 'exchange_rate' => 4000]);

        $key = md5($product->id.'|medium|||');
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

    public function test_checkout_fails_when_cash_usd_exceeds_limit(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 150,
                'cash_riel' => 0,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded'));
        $response->assertSessionHasErrors('cash_usd');
    }

    public function test_checkout_fails_when_cash_riel_exceeds_limit(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 0,
                'cash_riel' => 500000,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded'));
        $response->assertSessionHasErrors('cash_riel');
    }

    public function test_checkout_rejects_non_numeric_values(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 'abc',
                'cash_riel' => 'xyz',
                'discount' => 'ten',
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded'));
        $response->assertSessionHasErrors(['cash_usd', 'cash_riel', 'discount']);
    }

    public function test_checkout_rejects_negative_values(): void
    {
        [$user, $cart] = $this->setupCart();

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => -1,
                'cash_riel' => -100,
                'discount' => -5,
            ]);

        $response->assertSessionHas('error', __('messages.payment_limit_exceeded'));
        $response->assertSessionHasErrors(['cash_usd', 'cash_riel', 'discount']);
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
    
    public function test_tiered_payment_limits_for_usd(): void
    {
        foreach ([150 => 200, 250 => 300] as $total => $limit) {
            [$user, $cart] = $this->setupCart($total);

            $fail = $this->actingAs($user)
                ->withSession(['cart' => $cart])
                ->post('/cashier/pos/checkout', [
                    'method'    => 'cash',
                    'cash_usd'  => $limit + 1,
                    'cash_riel' => 0,
                ]);

            $fail->assertSessionHas('error', __('messages.payment_limit_exceeded'));
            $fail->assertSessionHasErrors('cash_usd');

            $pass = $this->actingAs($user)
                ->withSession(['cart' => $cart])
                ->post('/cashier/pos/checkout', [
                    'method'    => 'cash',
                    'cash_usd'  => $limit,
                    'cash_riel' => 0,
                ]);

            $sale = Sale::latest()->first();
            $pass->assertRedirect(route('cashier.invoice.print', ['sale' => $sale->id, 'auto' => 1]));
        }
    }

    public function test_tiered_payment_limits_for_riel(): void
    {
        $exchangeRate = 4000; // from setupCart settings

        foreach ([150 => 200, 250 => 300] as $total => $limitUsd) {
            [$user, $cart] = $this->setupCart($total);
            $limitRiel = $limitUsd * $exchangeRate;

            $fail = $this->actingAs($user)
                ->withSession(['cart' => $cart])
                ->post('/cashier/pos/checkout', [
                    'method'    => 'cash',
                    'cash_usd'  => 0,
                    'cash_riel' => $limitRiel + 1,
                ]);

            $fail->assertSessionHas('error', __('messages.payment_limit_exceeded'));
            $fail->assertSessionHasErrors('cash_riel');

            $pass = $this->actingAs($user)
                ->withSession(['cart' => $cart])
                ->post('/cashier/pos/checkout', [
                    'method'    => 'cash',
                    'cash_usd'  => 0,
                    'cash_riel' => $limitRiel,
                ]);

            $sale = Sale::latest()->first();
            $pass->assertRedirect(route('cashier.invoice.print', ['sale' => $sale->id, 'auto' => 1]));
        }
    }
}