<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockLogCategoryDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): array
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Coffee']);
        $product = Product::create([
            'name' => 'Latte',
            'price' => 5,
            'category_id' => $category->id,
            'stock' => 0,
        ]);
        $log = StockLog::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 1,
            'note' => null,
            'user_id' => $user->id,
        ]);

        return [$user, $category, $product, $log];
    }

    public function test_index_displays_category_name(): void
    {
        [$user, $category] = $this->seedData();

        $response = $this->actingAs($user)->get('/admin/stock-logs');

        $response->assertOk();
        $response->assertSee($category->name);
    }

    public function test_export_csv_includes_category(): void
    {
        [$user, $category] = $this->seedData();

        $response = $this->actingAs($user)->get('/admin/stock-logs/export');
        $content = $response->streamedContent();

        $this->assertStringContainsString('Category', $content);
        $this->assertStringContainsString($category->name, $content);
    }

    public function test_export_pdf_includes_category(): void
    {
        [$user, $category] = $this->seedData();

        SnappyPdf::shouldReceive('loadHTML')->once()->withArgs(function ($html) use ($category) {
            return str_contains($html, 'Category') && str_contains($html, $category->name);
        })->andReturnSelf();
        SnappyPdf::shouldReceive('setOption')->andReturnSelf();
        SnappyPdf::shouldReceive('download')->andReturn(response('pdf'));

        $response = $this->actingAs($user)->get('/admin/stock-logs/pdf');
        $response->assertOk();
    }
}