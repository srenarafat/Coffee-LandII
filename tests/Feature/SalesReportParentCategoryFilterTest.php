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

class SalesReportParentCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_parent_category_includes_child_sales(): void
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $parent = Category::create(['name' => 'Drinks']);
        $child  = Category::create(['name' => 'Coffee', 'parent_id' => $parent->id]);
        $other  = Category::create(['name' => 'Snacks']);

        $childProduct = Product::create([
            'name' => 'Latte',
            'price' => 5,
            'category_id' => $child->id,
            'shop_id' => $shop->id,
            'stock' => 1,
        ]);
        $otherProduct = Product::create([
            'name' => 'Cookie',
            'price' => 2,
            'category_id' => $other->id,
            'shop_id' => $shop->id,
            'stock' => 1,
        ]);

        $saleChild = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 5,
            'discount' => 0,
            'total' => 5,
            'invoice_no' => 'INV-C',
        ]);
        $saleOther = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 2,
            'discount' => 0,
            'total' => 2,
            'invoice_no' => 'INV-O',
        ]);

        SaleItem::create([
            'sale_id' => $saleChild->id,
            'product_id' => $childProduct->id,
            'quantity' => 1,
            'price' => 5,
            'total' => 5,
        ]);
        SaleItem::create([
            'sale_id' => $saleOther->id,
            'product_id' => $otherProduct->id,
            'quantity' => 1,
            'price' => 2,
            'total' => 2,
        ]);

        $response = $this->actingAs($user)->get('/admin/sales-report?category_id=' . $parent->id);

        $response->assertOk();
        $response->assertSee('INV-C');
        $response->assertDontSee('INV-O');
    }
}