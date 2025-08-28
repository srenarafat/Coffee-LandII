<?php

namespace Tests\Feature;

use App\Models\{Category, Product, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImportPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_with_import_price(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Beverages']);

        $response = $this->actingAs($user)->post(route('admin.products.store'), [
            'name' => 'Espresso',
            'price' => 5,
            'import_price' => 3.5,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Espresso',
            'import_price' => 3.5,
        ]);

        $index = $this->actingAs($user)->get(route('admin.products.index'));
        $index->assertSee('$' . number_format(3.5, 2));
    }

    public function test_admin_can_update_product_import_price(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Beverages']);
        $product = Product::create([
            'name' => 'Latte',
            'price' => 6,
            'import_price' => 2.5,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => 'Latte',
                'price' => 6,
                'import_price' => 4.25,
                'category_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'import_price' => 4.25,
        ]);

        $index = $this->actingAs($user)->get(route('admin.products.index'));
        $index->assertSee('$' . number_format(4.25, 2));
    }
}