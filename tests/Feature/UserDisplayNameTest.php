<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDisplayNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_accessor_returns_formatted_name(): void
    {
        // Dengan honorific "Bpk" dan full_name "Irwan Wardianto"
        $userWithHonorific = new User([
            'honorific' => 'Bpk',
            'full_name' => 'Irwan Wardianto',
        ]);
        $this->assertEquals('Bpk Irwan Wardianto', $userWithHonorific->display_name);

        // Tanpa honorific (null) hanya full_name
        $userWithoutHonorific = new User([
            'honorific' => null,
            'full_name' => 'Irwan Wardianto',
        ]);
        $this->assertEquals('Irwan Wardianto', $userWithoutHonorific->display_name);

        // Tanpa honorific (string kosong) hanya full_name
        $userEmptyHonorific = new User([
            'honorific' => '',
            'full_name' => 'Irwan Wardianto',
        ]);
        $this->assertEquals('Irwan Wardianto', $userEmptyHonorific->display_name);
    }

    public function test_dirut_pages_contain_bpk_irwan_wardianto(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'honorific' => 'Bpk',
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        // Halaman /dirut/dashboard
        $dashResponse = $this->actingAs($dirut)->get('/dirut/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Bpk Irwan Wardianto');

        // Halaman /dirut/agenda (Kalender)
        $agendaResponse = $this->actingAs($dirut)->get('/dirut/agenda?view=kalender');
        $agendaResponse->assertStatus(200);
        $agendaResponse->assertSee('Bpk Irwan Wardianto');

        // Halaman /dirut/agenda (Daftar)
        $agendaDaftarResponse = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar');
        $agendaDaftarResponse->assertStatus(200);
        $agendaDaftarResponse->assertSee('Bpk Irwan Wardianto');
    }

    public function test_admin_user_form_validation_rejects_honorific_longer_than_20_chars(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'is_active' => true,
        ]);

        // 1. Store dengan honorific > 20 karakter
        $responseStore = $this->actingAs($admin)->post('/admin/users', [
            'email' => 'newuser@samara.test',
            'honorific' => str_repeat('A', 21), // 21 karakter
            'full_name' => 'Budi Santoso',
            'role' => 'TIM',
            'password' => 'password123',
        ]);
        $responseStore->assertSessionHasErrors('honorific');

        // 2. Store dengan honorific valid (<= 20 karakter)
        $responseValidStore = $this->actingAs($admin)->post('/admin/users', [
            'email' => 'validuser@samara.test',
            'honorific' => 'Bpk Dr.',
            'full_name' => 'Budi Santoso',
            'role' => 'TIM',
            'password' => 'password123',
        ]);
        $responseValidStore->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'validuser@samara.test',
            'honorific' => 'Bpk Dr.',
            'full_name' => 'Budi Santoso',
        ]);

        $createdUser = User::where('email', 'validuser@samara.test')->firstOrFail();

        // 3. Update dengan honorific > 20 karakter
        $responseUpdate = $this->actingAs($admin)->patch("/admin/users/{$createdUser->id}", [
            'email' => 'validuser@samara.test',
            'honorific' => str_repeat('X', 21),
            'full_name' => 'Budi Santoso Updated',
            'role' => 'TIM',
        ]);
        $responseUpdate->assertSessionHasErrors('honorific');
    }
}
