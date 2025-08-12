<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSalesReportFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sales_report_filter_includes_superadmins(): void
    {
        $shop = Shop::create(['name' => 'S1']);
        $admin = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $superadmin1 = User::factory()->create(['role' => 'superadmin', 'name' => 'SA1']);
        $superadmin2 = User::factory()->create(['role' => 'superadmin', 'name' => 'SA2']);

        $response = $this->actingAs($admin)->get('/admin/sales-report');

        $response->assertOk();
        $response->assertSee('Price Unit');
        $response->assertSee($superadmin1->name);
        $response->assertSee($superadmin2->name);
    }
}