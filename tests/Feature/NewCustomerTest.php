<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_first_purchase_today_is_new(): void
    {
        Carbon::setTestNow('2024-01-31');

        $shop = Shop::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $customer = Customer::factory()->for($shop)->create();

        Sale::factory()
            ->for($admin)
            ->for($shop)
            ->for($customer)
            ->create();

        $response = $this->actingAs($admin)->get('/admin/customers');

        $response->assertOk();
        $newCustomers = $response->viewData('newCustomers');
        $this->assertCount(1, $newCustomers);
        $this->assertTrue($newCustomers->contains($customer));
    }
}