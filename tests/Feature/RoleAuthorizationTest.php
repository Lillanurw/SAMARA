<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_menu(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value, 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_tim_cannot_access_admin_menu_returns_403(): void
    {
        $tim = User::factory()->create(['role' => UserRole::TIM->value, 'is_active' => true]);

        $response = $this->actingAs($tim)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_tim_cannot_access_director_review_returns_403(): void
    {
        $tim = User::factory()->create(['role' => UserRole::TIM->value, 'is_active' => true]);

        $response = $this->actingAs($tim)->get('/director-review');
        $response->assertStatus(403);
    }

    public function test_director_can_access_director_review_but_not_admin_menu(): void
    {
        $director = User::factory()->create(['role' => UserRole::DIRECTOR->value, 'is_active' => true]);

        $responseReview = $this->actingAs($director)->get('/director-review');
        $responseReview->assertStatus(200);

        $responseAdmin = $this->actingAs($director)->get('/admin/users');
        $responseAdmin->assertStatus(403);
    }
}
