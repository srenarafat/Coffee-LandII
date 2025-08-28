<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerScreenLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_screen_shows_khmer_after_switch(): void
    {
        config(['app.locale' => 'en']);

        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)->get('/lang/switch');

        $response = $this->actingAs($user)->get('/customer-screen');

        $response->assertOk();
        $response->assertSee('ការកម្មង់របស់អ្នក');
    }
}