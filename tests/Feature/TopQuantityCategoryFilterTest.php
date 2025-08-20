<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopQuantityCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): array
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $parent = Category::create(['name' => 'Drinks']);
        $child = Category::create(['name' => 'Coffee', 'parent_id' => $parent->id]);
        $other = Category::create(['name' => 'Snacks']);

        $childProduct = Product::create([
            'name' => 'Latte',
            'price' => 5,
            'category_id' => $child->id,
            'shop_id' => $shop->id,
            'stock' => 0,
        ]);
        $otherProduct = Product::create([
            'name' => 'Cookie',
            'price' => 2,
            'category_id' => $other->id,
            'shop_id' => $shop->id,
            'stock' => 0,
        ]);

        $sale = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 15,
            'discount' => 0,
            'total' => 15,
            'invoice_no' => 'INV-1',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $childProduct->id,
            'quantity' => 3,
            'price' => 5,
            'total' => 15,
        ]);

        $sale2 = Sale::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'subtotal' => 2,
            'discount' => 0,
            'total' => 2,
            'invoice_no' => 'INV-2',
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $otherProduct->id,
            'quantity' => 1,
            'price' => 2,
            'total' => 2,
        ]);

        return [$user, $parent, $childProduct, $otherProduct];
    }

    public function test_report_includes_subcategory_products_when_parent_selected(): void
    {
        [$user, $parent, $childProduct, $otherProduct] = $this->seedData();

        $response = $this->actingAs($user)->get('/admin/reports/top-quantity-sales?category_id=' . $parent->id);

        $response->assertOk();
        $response->assertSee($childProduct->name);
        $response->assertDontSee($otherProduct->name);
    }

    public function test_csv_export_includes_subcategory_products_when_parent_selected(): void
    {
        [$user, $parent, $childProduct, $otherProduct] = $this->seedData();

        $response = $this->actingAs($user)->get('/admin/reports/top-quantity-sales/export?filter=all&category_id=' . $parent->id);
        $content = $response->streamedContent();

        $this->assertStringContainsString($childProduct->name, $content);
        $this->assertStringNotContainsString($otherProduct->name, $content);
    }

    public function test_pdf_export_includes_subcategory_products_when_parent_selected(): void
    {
        [$user, $parent, $childProduct, $otherProduct] = $this->seedData();

        SnappyPdf::shouldReceive('loadHTML')->once()->withArgs(function ($html) use ($childProduct, $otherProduct) {
            return str_contains($html, $childProduct->name) && !str_contains($html, $otherProduct->name);
        })->andReturnSelf();
        SnappyPdf::shouldReceive('setOption')->once()->with('encoding', 'UTF-8')->andReturnSelf();
        SnappyPdf::shouldReceive('setOption')->once()->with('enable-local-file-access', true)->andReturnSelf();
        SnappyPdf::shouldReceive('download')->andReturn(response('pdf'));

        $response = $this->actingAs($user)->get('/admin/reports/top-quantity-sales/pdf?filter=all&category_id=' . $parent->id);
        $response->assertOk();
    }
}