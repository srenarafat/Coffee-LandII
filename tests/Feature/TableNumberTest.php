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

class TableNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_shows_table_number()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create(['name' => 'P', 'price' => 1, 'category_id' => $category->id, 'shop_id' => $shop->id, 'stock' => 10]);

        $sale = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'table_number' => 5,
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

        $response = $this->actingAs($user)->get("/cashier/invoice/{$sale->id}/print");
        $response->assertOk();
        $response->assertSee('Table: 5');
    }
    
    public function test_table_number_session_cleared_after_auto_print()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create(['name' => 'P', 'price' => 1, 'category_id' => $category->id, 'shop_id' => $shop->id, 'stock' => 10]);

        $sale = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'table_number' => 5,
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

        $response = $this->actingAs($user)->withSession(['table_number' => 5])->get("/cashier/invoice/{$sale->id}/print?auto=1");
        $response->assertOk();
        $this->assertNull(session('table_number'));
    }
}