<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesDocumentPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_document_pdf_returns_document_layout(): void
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 10,
        ]);

        $sale = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 1,
            'discount' => 0,
            'total' => 1,
            'invoice_no' => 'INV-1',
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

        SnappyPdf::shouldReceive('loadHTML')->once()->withArgs(function ($html) {
            return str_contains($html, 'Sale detail (document view)');
        })->andReturnSelf();
        SnappyPdf::shouldReceive('setOption')->once()->with('encoding', 'UTF-8')->andReturnSelf();
        SnappyPdf::shouldReceive('setOption')->once()->with('enable-local-file-access', true)->andReturnSelf();
        SnappyPdf::shouldReceive('download')->andReturn(response('pdf'));

        $response = $this->actingAs($user)->get(route('sales.document.pdf', ['role' => 'admin', 'sale' => $sale->id]));
        $response->assertOk();
    }
}