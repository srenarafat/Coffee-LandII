<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAtRiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_with_no_order_for_over_30_days_is_at_risk(): void
    {
        Carbon::setTestNow('2024-01-31');

        $shop = Shop::create(['name' => 'S1']);
        $admin = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Old Customer',
        ]);

        Sale::create([
            'user_id' => $admin->id,
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'subtotal' => 10,
            'discount' => 0,
            'total' => 10,
            'invoice_no' => 'INV-001',
            'created_at' => Carbon::now()->subDays(31),
            'updated_at' => Carbon::now()->subDays(31),
        ]);

        $response = $this->actingAs($admin)->get('/admin/customers');

        $response->assertOk();
        $atRisk = $response->viewData('atRiskCustomers');
        $this->assertCount(1, $atRisk);
        $this->assertTrue($atRisk->contains($customer));
    }
}