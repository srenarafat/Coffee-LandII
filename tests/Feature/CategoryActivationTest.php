<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_activate_and_deactivate_category()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Beverages', 'is_active' => false]);

        $this->actingAs($user)
            ->from('/admin/categories')
            ->patch(route('admin.categories.activate', $category))
            ->assertRedirect('/admin/categories');

        $this->assertTrue($category->fresh()->is_active);

        $this->actingAs($user)
            ->from('/admin/categories')
            ->patch(route('admin.categories.deactivate', $category))
            ->assertRedirect('/admin/categories');

        $this->assertFalse($category->fresh()->is_active);
    }
}