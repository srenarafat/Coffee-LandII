<?php

namespace Tests\Feature;

use App\Models\{Category, Product, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_rejects_if_category_tree_inactive()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $parent = Category::create(['name' => 'Parent', 'is_active' => false]);
        $child = Category::create(['name' => 'Child', 'parent_id' => $parent->id, 'is_active' => true]);

        $response = $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'name' => 'Latte',
                'price' => 5,
                'category_id' => $child->id,
            ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('products', ['name' => 'Latte']);
    }

    public function test_update_rejects_if_category_tree_inactive()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $activeParent = Category::create(['name' => 'Active Parent', 'is_active' => true]);
        $active = Category::create(['name' => 'Active', 'parent_id' => $activeParent->id, 'is_active' => true]);
        $inactiveParent = Category::create(['name' => 'Inactive Parent', 'is_active' => false]);
        $inactiveChild = Category::create(['name' => 'Inactive Child', 'parent_id' => $inactiveParent->id, 'is_active' => true]);

        $product = Product::create(['name' => 'Tea', 'price' => 3, 'category_id' => $active->id]);

        $response = $this->actingAs($user)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => 'Tea',
                'price' => 3,
                'category_id' => $inactiveChild->id,
            ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertEquals($active->id, $product->fresh()->category_id);
    }
}