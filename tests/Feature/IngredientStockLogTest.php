<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ingredient;
use App\Models\IngredientStockLog;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientStockLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_ingredient_stock_log(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Milk', 'unit' => 'L', 'stock' => 0]);

        $response = $this->actingAs($user)->post('/admin/ingredient-stock', [
            'ingredient_id' => $ingredient->id,
            'type' => 'in',
            'quantity' => 5,
            'note' => 'Added milk',
        ]);

        $response->assertRedirect('/admin/ingredient-stock');
        $this->assertDatabaseHas('ingredient_stock_logs', [
            'ingredient_id' => $ingredient->id,
            'type' => 'in',
            'quantity' => 5,
            'unit' => 'L',
            'note' => 'Added milk',
        ]);
        
        $ingredient->refresh();
        $this->assertEquals(5, $ingredient->stock);
    }

    public function test_index_can_filter_by_type(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg', 'stock' => 0]);
        IngredientStockLog::create(['ingredient_id' => $ingredient->id, 'type' => 'in', 'quantity' => 2, 'unit' => 'kg', 'note' => null, 'user_id' => $user->id]);
        IngredientStockLog::create(['ingredient_id' => $ingredient->id, 'type' => 'out', 'quantity' => 1, 'unit' => 'kg', 'note' => null, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/admin/ingredient-stock?type=in');

        $response->assertOk();
        $response->assertSee('IN');
        $response->assertDontSee('OUT');
    }

    public function test_export_csv_includes_ingredient(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Milk', 'unit' => 'L', 'stock' => 0]);
        IngredientStockLog::create(['ingredient_id' => $ingredient->id, 'type' => 'in', 'quantity' => 3, 'unit' => 'L', 'note' => null, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/admin/ingredient-stock/export');
        $content = $response->streamedContent();

        $this->assertStringContainsString('Ingredient', $content);
        $this->assertStringContainsString('Milk', $content);
    }

    public function test_export_pdf_includes_ingredient(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Milk', 'unit' => 'L', 'stock' => 0]);
        IngredientStockLog::create(['ingredient_id' => $ingredient->id, 'type' => 'in', 'quantity' => 3, 'unit' => 'L', 'note' => null, 'user_id' => $user->id]);

        SnappyPdf::shouldReceive('loadHTML')->once()->withArgs(function ($html) use ($ingredient) {
            return str_contains($html, 'Ingredient') && str_contains($html, $ingredient->name);
        })->andReturnSelf();
        SnappyPdf::shouldReceive('setOption')->andReturnSelf();
        SnappyPdf::shouldReceive('download')->andReturn(response('pdf'));

        $response = $this->actingAs($user)->get('/admin/ingredient-stock/pdf');
        $response->assertOk();
    }
    
    public function test_cannot_reduce_stock_below_zero(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create(['name' => 'Butter', 'unit' => 'kg', 'stock' => 0]);

        $response = $this->actingAs($user)->post('/admin/ingredient-stock', [
            'ingredient_id' => $ingredient->id,
            'type' => 'out',
            'quantity' => 1,
            'note' => null,
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertDatabaseCount('ingredient_stock_logs', 0);
        $this->assertEquals(0, $ingredient->fresh()->stock);
    }
}