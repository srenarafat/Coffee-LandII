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

class CashierSalesHistoryParentCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_sales_history_filters_by_parent_category(): void
    {
        $shop = Shop::create(['name' => 'Shop1']);
        $cashier = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);

        $parent = Category::create(['name' => 'Food', 'shop_id' => $shop->id]);
        $child  = Category::create(['name' => 'Snacks', 'parent_id' => $parent->id, 'shop_id' => $shop->id]);
        $other  = Category::create(['name' => 'Drinks', 'shop_id' => $shop->id]);

        $childProduct = Product::create([
            'name' => 'Chips',
            'price' => 3,
            'category_id' => $child->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);
        $otherProduct = Product::create([
            'name' => 'Soda',
            'price' => 2,
            'category_id' => $other->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);

        $saleChild = Sale::create([
            'user_id' => $cashier->id,
            'shop_id' => $shop->id,
            'subtotal' => 3,
            'discount' => 0,
            'total' => 3,
            'invoice_no' => 'INV-SNACK',
        ]);
        $saleOther = Sale::create([
            'user_id' => $cashier->id,
            'shop_id' => $shop->id,
            'subtotal' => 2,
            'discount' => 0,
            'total' => 2,
            'invoice_no' => 'INV-DRINK',
        ]);

        SaleItem::create([
            'sale_id' => $saleChild->id,
            'product_id' => $childProduct->id,
            'quantity' => 1,
            'price' => 3,
            'total' => 3,
        ]);
        SaleItem::create([
            'sale_id' => $saleOther->id,
            'product_id' => $otherProduct->id,
            'quantity' => 1,
            'price' => 2,
            'total' => 2,
        ]);

        $response = $this->actingAs($cashier)->get('/cashier/sales-history?category_id=' . $parent->id);

        $response->assertOk();
        $response->assertSee('INV-SNACK');
        $response->assertDontSee('INV-DRINK');
    }
}