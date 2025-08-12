<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_dashboard_and_manage_users(): void
    {
        $this->seed(UserSeeder::class);
        $superadmin = User::where('role', 'superadmin')->first();

        $this->actingAs($superadmin)
            ->get('/superadmin/dashboard')
            ->assertOk();

        $this->actingAs($superadmin)
            ->get('/superadmin/users')
            ->assertOk();

        $response = $this->actingAs($superadmin)->post('/superadmin/users', [
            'name' => 'Super Admin',
            'email' => 'admin2@example.com',
            'role' => 'admin',
            'password' => '123456', 
        ]);

        $response->assertRedirect('/superadmin/users');
        $this->assertDatabaseHas('users', ['email' => 'admin2@example.com']);
    }
}