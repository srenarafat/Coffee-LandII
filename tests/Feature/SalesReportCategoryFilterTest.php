<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_filters_by_category_and_displays_category_name(): void
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $catA = Category::create(['name' => 'Coffee']);
        $catB = Category::create(['name' => 'Tea']);

        $prodA = Product::create(['name' => 'Latte', 'price' => 5, 'category_id' => $catA->id, 'shop_id' => $shop->id, 'stock' => 0]);
        $prodB = Product::create(['name' => 'Green Tea', 'price' => 4, 'category_id' => $catB->id, 'shop_id' => $shop->id, 'stock' => 0]);

        $saleA = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 5,
            'discount' => 0,
            'total' => 5,
            'invoice_no' => 'INV-A',
        ]);

        $saleB = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 4,
            'discount' => 0,
            'total' => 4,
            'invoice_no' => 'INV-B',
        ]);

        SaleItem::create(['sale_id' => $saleA->id, 'product_id' => $prodA->id, 'quantity' => 1, 'price' => 5, 'total' => 5]);
        SaleItem::create(['sale_id' => $saleB->id, 'product_id' => $prodB->id, 'quantity' => 1, 'price' => 4, 'total' => 4]);

        $response = $this->actingAs($user)->get('/admin/sales-report?category_id=' . $catA->id);

        $response->assertOk();
        $response->assertSee('Price Unit');
        $response->assertSee('INV-A');
        $response->assertDontSee('INV-B');
        $response->assertSee('Latte - Coffee');
        $response->assertDontSee('Green Tea - Tea');
        $response->assertSee('$5.00');
        $response->assertDontSee('$4.00');
    }
}
