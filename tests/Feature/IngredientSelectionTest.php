<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_selecting_ingredient_populates_id_and_submits(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg', 'stock' => 0]);

        $response = $this->actingAs($user)->post('/admin/ingredient-stock', [
            'ingredient_id' => $ingredient->id,
            'type' => 'in',
            'quantity' => 1,
            'note' => null,
        ]);

        $response->assertRedirect('/admin/ingredient-stock');
        $this->assertDatabaseHas('ingredient_stock_logs', [
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
            'unit' => 'kg',
        ]);
    }
    
    public function test_submitting_with_only_ingredient_name_is_processed(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg', 'stock' => 0]);

        $response = $this->actingAs($user)->post('/admin/ingredient-stock', [
            'ingredient_name' => 'Sugar',
            'type' => 'in',
            'quantity' => 2,
            'note' => null,
        ]);

        $response->assertRedirect('/admin/ingredient-stock');
        $this->assertDatabaseHas('ingredient_stock_logs', [
            'ingredient_id' => $ingredient->id,
            'quantity' => 2,
            'unit' => 'kg',
        ]);
    }
}
