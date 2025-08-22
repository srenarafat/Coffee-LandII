<?php

namespace Tests\Feature;

use App\Models\{Product, Category, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_lists_newest_first()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Drinks']);

        $old = Product::create(['name' => 'Old', 'price' => 1, 'category_id' => $category->id]);
        $old->created_at = now()->subDay();
        $old->save();

        $new = Product::create(['name' => 'New', 'price' => 2, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('admin.products.index'));

        $response->assertSeeInOrder(['New', 'Old']);
    }

    public function test_ajax_search_uses_latest_ordering()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Drinks']);

        $old = Product::create(['name' => 'Old Latte', 'price' => 1, 'category_id' => $category->id]);
        $old->created_at = now()->subDay();
        $old->save();

        $new = Product::create(['name' => 'New Latte', 'price' => 2, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->get(
            route('admin.products.index', ['search' => 'Latte']),
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertSeeInOrder(['New Latte', 'Old Latte']);
    }
}