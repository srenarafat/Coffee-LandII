<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosInactiveAncestorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_grid_excludes_products_with_inactive_parent_category(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $inactiveParent = Category::create(['name' => 'InactiveParent', 'is_active' => false]);
        $child = Category::create([
            'name' => 'Child',
            'parent_id' => $inactiveParent->id,
            'is_active' => true,
        ]);
        $blockedProduct = Product::create([
            'name' => 'Blocked',
            'price' => 5,
            'category_id' => $child->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);

        $root = Category::create(['name' => 'Root', 'is_active' => true]);
        $activeChild = Category::create([
            'name' => 'ActiveChild',
            'parent_id' => $root->id,
            'is_active' => true,
        ]);
        $visibleProduct = Product::create([
            'name' => 'Visible',
            'price' => 5,
            'category_id' => $activeChild->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/cashier/pos');
        $response->assertOk();
        $response->assertSee($visibleProduct->name);
        $response->assertDontSee($blockedProduct->name);
    }

    public function test_pos_grid_excludes_products_with_inactive_grandparent_category(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $grand = Category::create(['name' => 'Grand', 'is_active' => false]);
        $parent = Category::create([
            'name' => 'Parent',
            'parent_id' => $grand->id,
            'is_active' => true,
        ]);
        $child = Category::create([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);
        $deepProduct = Product::create([
            'name' => 'Deep',
            'price' => 5,
            'category_id' => $child->id,
            'stock' => 10,
            'image' => 'img.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->get('/cashier/pos');
        $response->assertOk();
        $response->assertDontSee($deepProduct->name);
    }
}