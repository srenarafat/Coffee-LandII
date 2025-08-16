<?php

namespace Tests\Feature;

use App\Models\{Shop, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerNotesPersistTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_notes_are_saved(): void
    {
        $shop = Shop::create(['name' => 'S1']);
        $user = User::factory()->create(['role' => 'admin', 'shop_id' => $shop->id]);

        $response = $this->actingAs($user)->postJson('/admin/customers', [
            'name'  => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.com',
            'notes' => 'Frequent visitor',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('customers', [
            'shop_id' => $shop->id,
            'name'    => 'John Doe',
            'notes'   => 'Frequent visitor',
        ]);
    }
}