<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_switch_persists_across_routes(): void
    {
        // Start with English locale
        config(['app.locale' => 'en']);

        $user = User::factory()->create(['role' => 'admin']);

        // Ensure page shows English before switching
        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertSee('Dashboard');

        // Toggle to Khmer
        $this->actingAs($user)
            ->from('/admin/dashboard')
            ->get('/lang/switch')
            ->assertRedirect('/admin/dashboard');

        // Locale should persist and show Khmer labels on subsequent pages
        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertSee('ផ្ទាំងគ្រប់គ្រង');

        $this->actingAs($user)
            ->get('/admin/categories')
            ->assertSee('ប្រភេទ');
    }
}