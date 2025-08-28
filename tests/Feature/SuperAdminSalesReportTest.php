<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_all_sales()
    {
        $shop1 = Shop::create(['name' => 'S1']);
        $shop2 = Shop::create(['name' => 'S2']);

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $admin1 = User::factory()->create(['role' => 'admin', 'shop_id' => $shop1->id]);
        $admin2 = User::factory()->create(['role' => 'admin', 'shop_id' => $shop2->id]);

        Sale::create([
            'user_id'    => $admin1->id,
            'shop_id'    => $shop1->id,
            'subtotal'   => 10,
            'discount'   => 0,
            'total'      => 10,
            'invoice_no' => 'INV-001',
        ]);

        Sale::create([
            'user_id'    => $admin2->id,
            'shop_id'    => $shop2->id,
            'subtotal'   => 20,
            'discount'   => 0,
            'total'      => 20,
            'invoice_no' => 'INV-002',
        ]);

        $response = $this->actingAs($superadmin)->get('/admin/sales-report');

        $response->assertOk();
        $response->assertSee('Price Unit');
        $response->assertSee('INV-001');
        $response->assertSee('INV-002');
    }

    
    public function test_sales_report_filter_includes_other_superadmins(): void
    {
        $superadmin1 = User::factory()->create(['role' => 'superadmin', 'name' => 'SA1']);
        $superadmin2 = User::factory()->create(['role' => 'superadmin', 'name' => 'SA2']);

        $response = $this->actingAs($superadmin1)->get('/admin/sales-report');

        $response->assertOk();
        $response->assertSee('Price Unit');
        $response->assertSee($superadmin2->name);
    }
    
    public function test_sale_item_size_displayed_in_report_and_csv(): void
    {
        $shop = Shop::create(['name' => 'S1']);
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $category = Category::create(['name' => 'Coffee']);
        $product = Product::create([
            'name' => 'Latte',
            'price' => 5,
            'category_id' => $category->id,
            'stock' => 0,
        ]);

        $sale = Sale::create([
            'user_id' => $superadmin->id,
            'shop_id' => $shop->id,
            'subtotal' => 10,
            'discount' => 0,
            'total' => 10,
            'invoice_no' => 'INV-100',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 5,
            'total' => 10,
            'size' => 's',
        ]);

        $response = $this->actingAs($superadmin)->get('/admin/sales-report');
        $response->assertOk();
        $expected = 'Latte (S) x2';
        $response->assertSee($expected);
        

        $csv = $this->actingAs($superadmin)->get('/admin/reports/sales/export');
        $content = $csv->streamedContent();
        $this->assertStringContainsString($expected, $content);
    }
}

