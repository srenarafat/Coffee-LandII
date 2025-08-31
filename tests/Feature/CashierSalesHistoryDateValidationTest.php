<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierSalesHistoryDateValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_dates_return_validation_errors(): void
    {
        $shop = Shop::create(['name' => 'Shop1']);
        $cashier = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);

        $response = $this->actingAs($cashier)->get('/cashier/sales-history?from=bad&to=also-bad');

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['from', 'to']);
    }
}