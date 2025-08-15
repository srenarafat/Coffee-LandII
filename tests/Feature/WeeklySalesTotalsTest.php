<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklySalesTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_weekly_totals_match_weekly_sales_card(): void
    {
        Carbon::setTestNow('2024-01-03');

        $shop = Shop::create(['name' => 'S1']);
        $admin = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        Sale::create([
            'user_id' => $admin->id,
            'shop_id' => $shop->id,
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'created_at' => Carbon::parse('2024-01-01'),
            'updated_at' => Carbon::parse('2024-01-01'),
        ]);

        Sale::create([
            'user_id' => $admin->id,
            'shop_id' => $shop->id,
            'subtotal' => 50,
            'discount' => 0,
            'total' => 50,
            'created_at' => Carbon::parse('2024-01-03'),
            'updated_at' => Carbon::parse('2024-01-03'),
        ]);

        Sale::create([
            'user_id' => $admin->id,
            'shop_id' => $shop->id,
            'subtotal' => 75,
            'discount' => 0,
            'total' => 75,
            'created_at' => Carbon::parse('2024-01-06'),
            'updated_at' => Carbon::parse('2024-01-06'),
        ]);

        $expected = 225;

        $dashboard = $this->actingAs($admin)->get('/admin/dashboard');
        $weekSalesTotal = $dashboard->viewData('weekSalesTotal');

        $salesData = $this->actingAs($admin)->getJson('/admin/dashboard/sales-data/week')
            ->json('totals');

        $this->assertEquals($expected, $weekSalesTotal);
        $this->assertEquals($expected, array_sum($salesData));
    }

    public function test_superadmin_weekly_totals_match_weekly_sales_card(): void
    {
        Carbon::setTestNow('2024-01-03');

        $superadmin = User::factory()->create(['role' => 'superadmin']);

        Sale::create([
            'user_id' => $superadmin->id,
            'shop_id' => null,
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'created_at' => Carbon::parse('2024-01-01'),
            'updated_at' => Carbon::parse('2024-01-01'),
        ]);

        Sale::create([
            'user_id' => $superadmin->id,
            'shop_id' => null,
            'subtotal' => 50,
            'discount' => 0,
            'total' => 50,
            'created_at' => Carbon::parse('2024-01-03'),
            'updated_at' => Carbon::parse('2024-01-03'),
        ]);

        Sale::create([
            'user_id' => $superadmin->id,
            'shop_id' => null,
            'subtotal' => 75,
            'discount' => 0,
            'total' => 75,
            'created_at' => Carbon::parse('2024-01-06'),
            'updated_at' => Carbon::parse('2024-01-06'),
        ]);

        $expected = 225;

        $dashboard = $this->actingAs($superadmin)->get('/superadmin/dashboard');
        $weekSalesTotal = $dashboard->viewData('weekSalesTotal');

        $salesData = $this->actingAs($superadmin)->getJson('/superadmin/dashboard/sales-data/week')
            ->json('totals');

        $this->assertEquals($expected, $weekSalesTotal);
        $this->assertEquals($expected, array_sum($salesData));
    }
}
