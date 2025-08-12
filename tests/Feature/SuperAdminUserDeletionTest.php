<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminUserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_cannot_delete_self(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)
            ->from(route('superadmin.users.index'))
            ->delete(route('superadmin.users.destroy', $superadmin));

        $response->assertRedirect(route('superadmin.users.index'));
        $response->assertSessionHas('error', __('messages.cannot_delete_self'));
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_superadmin_cannot_delete_another_superadmin(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $otherSuperadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)
            ->from(route('superadmin.users.index'))
            ->delete(route('superadmin.users.destroy', $otherSuperadmin));

        $response->assertRedirect(route('superadmin.users.index'));
        $response->assertSessionHas('error', __('messages.cannot_delete_superadmin'));
        $this->assertDatabaseHas('users', ['id' => $otherSuperadmin->id]);
    }
}