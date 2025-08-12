<?php

namespace Tests\Feature;

use App\Models\{User,Shop,Category,Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_notes_are_saved_with_sale_item()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 10
        ]);

        $cart = [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'notes' => ['Hot', 'Takeaway'],
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post('/cashier/pos/checkout', [
                'method' => 'cash',
                'cash_usd' => 1
            ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'notes' => json_encode(['Hot', 'Takeaway']),
        ]);
    }

    public function test_notes_can_be_added_via_update_route()
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);
        $category = Category::create(['name' => 'C']);
        $product = Product::create([
            'name' => 'P',
            'price' => 1,
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'stock' => 10
        ]);

        $this->actingAs($user)
            ->post('/cashier/pos/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/cashier/pos/note', ['product_id' => $product->id, 'note' => 'Hot']);
        $this->post('/cashier/pos/note', ['product_id' => $product->id, 'note' => 'Hot']);
        $this->post('/cashier/pos/note', ['product_id' => $product->id, 'note' => 'Takeaway']);

        $cart = session('cart');
        $this->assertEquals(['Hot', 'Takeaway'], $cart[$product->id]['notes']);
    }
}