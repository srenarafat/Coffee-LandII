<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturningCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_with_recent_sale_is_returning(): void
    {
        Carbon::setTestNow('2024-01-31');

        $shop = Shop::create(['name' => 'S1']);
        $admin = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Recent Customer',
        ]);

        Sale::create([
            'user_id' => $admin->id,
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'subtotal' => 10,
            'discount' => 0,
            'total' => 10,
            'invoice_no' => 'INV-002',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        $response = $this->actingAs($admin)->get('/admin/customers');

        $response->assertOk();
        $returning = $response->viewData('returningCustomers');
        $this->assertCount(1, $returning);
        $this->assertTrue($returning->contains($customer));
    }
}