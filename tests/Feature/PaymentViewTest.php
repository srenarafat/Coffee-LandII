<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_form_displays_correct_labels_and_currency(): void
    {
        // Setup user and basic setting
        $user = User::factory()->create(['role' => 'cashier']);
        Setting::create(['shop_name' => 'Shop', 'currency' => '$', 'discount_percent' => 0]);

        // Simulate cart in session so the payment page loads
        $cart = [
            'key' => [
                'product_id' => 1,
                'price'      => 10,
                'quantity'   => 1,
                'name'       => 'P',
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->get('/cashier/pos/payment');

        $response->assertOk();
        $response->assertSee('Cash Received');
        $response->assertSee('៛');
        $response->assertSee('Change');
        $response->assertDontSee('Cash Received /');
        $response->assertDontSee('Change /');
    }
}