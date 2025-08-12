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

class CashierSalesHistoryCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_sales_history_filters_by_category(): void
    {
        $shop = Shop::create(['name' => 'Shop1']);
        $cashier = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);

        $catA = Category::create(['name' => 'Coffee']);
        $catB = Category::create(['name' => 'Tea']);

        $prodA = Product::create([
            'name' => 'Latte',
            'price' => 5,
            'category_id' => $catA->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);
        $prodB = Product::create([
            'name' => 'Green Tea',
            'price' => 4,
            'category_id' => $catB->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);

        $saleA = Sale::create([
            'user_id' => $cashier->id,
            'shop_id' => $shop->id,
            'subtotal' => 5,
            'discount' => 0,
            'total' => 5,
            'invoice_no' => 'INV-A',
        ]);
        $saleB = Sale::create([
            'user_id' => $cashier->id,
            'shop_id' => $shop->id,
            'subtotal' => 4,
            'discount' => 0,
            'total' => 4,
            'invoice_no' => 'INV-B',
        ]);

        SaleItem::create([
            'sale_id' => $saleA->id,
            'product_id' => $prodA->id,
            'quantity' => 1,
            'price' => 5,
            'total' => 5,
        ]);
        SaleItem::create([
            'sale_id' => $saleB->id,
            'product_id' => $prodB->id,
            'quantity' => 1,
            'price' => 4,
            'total' => 4,
        ]);

        $response = $this->actingAs($cashier)->get('/cashier/sales-history?category_id=' . $catA->id);

        $response->assertOk();
        $response->assertSee('INV-A');
        $response->assertDontSee('INV-B');
    }
}