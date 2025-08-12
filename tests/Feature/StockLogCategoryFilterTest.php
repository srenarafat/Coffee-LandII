<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockLogCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_can_filter_by_category(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $catA = Category::create(['name' => 'Coffee']);
        $catB = Category::create(['name' => 'Tea']);
        $prodA = Product::create(['name' => 'Latte', 'price' => 5, 'category_id' => $catA->id, 'stock' => 0]);
        $prodB = Product::create(['name' => 'Green Tea', 'price' => 4, 'category_id' => $catB->id, 'stock' => 0]);
        StockLog::create(['product_id' => $prodA->id, 'type' => 'in', 'quantity' => 5, 'note' => null, 'user_id' => $user->id]);
        StockLog::create(['product_id' => $prodB->id, 'type' => 'in', 'quantity' => 3, 'note' => null, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/admin/stock-logs?category_id=' . $catA->id);

        $response->assertOk();
        $response->assertSee('Latte');
        $response->assertDontSee('Green Tea');
    }

    public function test_create_filters_products_by_category(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $catA = Category::create(['name' => 'Coffee']);
        $catB = Category::create(['name' => 'Tea']);
        $prodA = Product::create(['name' => 'Latte', 'price' => 5, 'category_id' => $catA->id, 'stock' => 0]);
        $prodB = Product::create(['name' => 'Green Tea', 'price' => 4, 'category_id' => $catB->id, 'stock' => 0]);

        $response = $this->actingAs($user)->get('/admin/stock-logs/create?category_id=' . $catA->id);

        $response->assertOk();
        $response->assertSee('Latte');
        $response->assertDontSee('Green Tea');
    }
}