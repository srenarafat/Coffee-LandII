<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierInvoiceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_view_other_cashiers_sale_invoice()
    {
        $shop = Shop::create(['name' => 'S1']);
        $owner = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $other = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);

        $sale = Sale::create([
            'user_id' => $owner->id,
            'shop_id' => $shop->id,
            'subtotal' => 1,
            'discount' => 0,
            'total' => 1,
            'invoice_no' => 'INV-001',
            'payment_method' => 'cash',
            'cash_usd' => 1,
            'cash_riel' => 0,
            'change_usd' => 0,
            'change_riel' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1,
            'total' => 1,
        ]);

        $response = $this->actingAs($other)->get(route('cashier.sales.invoice', $sale));
        $response->assertStatus(403);
    }
}