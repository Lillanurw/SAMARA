<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesAssignableTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_assignable_scope_filters_correctly(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $inactiveTim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'full_name' => 'Inactive Sales User',
            'is_active' => false,
        ]);

        $activeTim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'full_name' => 'Active Tim User',
            'is_active' => true,
        ]);

        $activeAdmin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'full_name' => 'Active Admin User',
            'is_active' => true,
        ]);

        $assignableUsers = User::salesAssignable()->pluck('id');

        $this->assertFalse($assignableUsers->contains($dirut->id));
        $this->assertFalse($assignableUsers->contains($inactiveTim->id));
        $this->assertTrue($assignableUsers->contains($activeTim->id));
        $this->assertTrue($assignableUsers->contains($activeAdmin->id));
    }

    public function test_director_review_page_does_not_contain_dirut_but_contains_active_tim(): void
    {
        $director = User::factory()->create([
            'role' => UserRole::DIRECTOR->value,
            'full_name' => 'Director Boss',
            'is_active' => true,
        ]);

        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $tim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'full_name' => 'Ahmad Subagyo Tim',
            'is_active' => true,
        ]);

        $response = $this->actingAs($director)->get(route('director.review'));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Subagyo Tim');
        $response->assertDontSee('Irwan Wardianto');
    }

    public function test_director_store_validation_rejects_dirut_and_accepts_tim(): void
    {
        $director = User::factory()->create([
            'role' => UserRole::DIRECTOR->value,
            'full_name' => 'Director Boss',
            'is_active' => true,
        ]);

        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $tim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'full_name' => 'Ahmad Subagyo Tim',
            'is_active' => true,
        ]);

        // 1. Post dengan assigned_to = id Dirut harus ditolak validasi
        $responseDirut = $this->actingAs($director)->post(route('director.store'), [
            'topic' => 'Follow up penting',
            'input_type' => \App\Enums\DirectorInputType::STRATEGIC_DIRECTION->value,
            'direction_text' => 'Segera follow up customer ini',
            'assigned_to' => $dirut->id,
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'priority' => 'HIGH',
        ]);

        $responseDirut->assertSessionHasErrors(['assigned_to']);
        $this->assertEquals(
            'Pengguna yang dipilih tidak valid.',
            session('errors')->first('assigned_to')
        );

        // 2. Post dengan assigned_to = id TIM harus berhasil
        $responseTim = $this->actingAs($director)->post(route('director.store'), [
            'topic' => 'Follow up penting untuk TIM',
            'input_type' => \App\Enums\DirectorInputType::STRATEGIC_DIRECTION->value,
            'direction_text' => 'Segera hubungi PIC klien',
            'assigned_to' => $tim->id,
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'priority' => 'HIGH',
        ]);

        $responseTim->assertSessionHasNoErrors();
        $this->assertDatabaseHas('director_inputs', [
            'topic' => 'Follow up penting untuk TIM',
            'assigned_to' => $tim->id,
        ]);
    }

    public function test_dropdowns_in_monthly_plan_report_and_customers_do_not_contain_dirut(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'full_name' => 'Super Admin',
            'is_active' => true,
        ]);

        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $tim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'full_name' => 'Budi Sales Staff',
            'is_active' => true,
        ]);

        $area = Area::create(['area_code' => 'JKT01', 'area_name' => 'Jakarta Barat', 'region' => 'DKI']);
        $segment = Segment::create(['segment_code' => 'SEG01', 'segment_name' => 'Retail']);
        $admin->areas()->attach($area->id);

        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'customer_name' => 'PT Pelanggan Setia',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $tim->id,
            'address' => 'Jl. Merdeka No. 10',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'contact_name' => 'Ibu Maria',
            'contact_position' => 'Purchasing',
        ]);

        $visitPlan = VisitPlan::create([
            'customer_id' => $customer->id,
            'area_id' => $area->id,
            'owner_id' => $tim->id,
            'plan_month' => now()->format('Y-m'),
            'planned_date' => now()->addDay()->format('Y-m-d'),
            'specific_objective' => 'Diskusi kontrak',
        ]);

        // Monthly Plan (/plans)
        $plansResponse = $this->actingAs($admin)->get(route('plans.index'));
        $plansResponse->assertStatus(200);
        $plansResponse->assertSee('Budi Sales Staff');
        $plansResponse->assertDontSee('Irwan Wardianto');

        // Reports Filter & Create (/reports & /reports/create)
        $reportsResponse = $this->actingAs($admin)->get(route('reports.index'));
        $reportsResponse->assertStatus(200);
        $reportsResponse->assertSee('Budi Sales Staff');
        $reportsResponse->assertDontSee('Irwan Wardianto');

        $reportsCreateResponse = $this->actingAs($admin)->get(route('reports.create', ['visit_plan_id' => $visitPlan->id]));
        $reportsCreateResponse->assertStatus(200);
        $reportsCreateResponse->assertSee('Budi Sales Staff');
        $reportsCreateResponse->assertDontSee('Irwan Wardianto');

        // Customers (/customers)
        $customersResponse = $this->actingAs($admin)->get(route('customers.index'));
        $customersResponse->assertStatus(200);
        $customersResponse->assertSee('Budi Sales Staff');
        $customersResponse->assertDontSee('Irwan Wardianto');
    }

    public function test_customer_store_rejects_dirut_as_owner(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'full_name' => 'Super Admin',
            'is_active' => true,
        ]);

        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $area = Area::create(['area_code' => 'JKT02', 'area_name' => 'Jakarta Timur', 'region' => 'DKI']);
        $segment = Segment::create(['segment_code' => 'SEG02', 'segment_name' => 'Distributor']);

        $response = $this->actingAs($admin)->post(route('customers.store'), [
            'customer_name' => 'Customer Baru Test',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $dirut->id,
            'address' => 'Jl. Sudirman',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'contact_name' => 'Kontak Test',
            'contact_position' => 'Staff',
            'priority_tier' => 'TIER_1',
        ]);

        $response->assertSessionHasErrors(['owner_id']);
        $this->assertEquals(
            'Pengguna yang dipilih tidak valid.',
            session('errors')->first('owner_id')
        );
    }

    public function test_visit_plan_store_rejects_dirut_as_companion_member(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'full_name' => 'Super Admin',
            'is_active' => true,
        ]);

        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $area = Area::create(['area_code' => 'JKT03', 'area_name' => 'Jakarta Selatan', 'region' => 'DKI']);
        $segment = Segment::create(['segment_code' => 'SEG03', 'segment_name' => 'Corporate']);
        $customer = Customer::create([
            'customer_code' => 'CUST-002',
            'customer_name' => 'PT Mitra Sejati',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $admin->id,
            'address' => 'Jl. Gatot Subroto',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'contact_name' => 'Pak Rudi',
            'contact_position' => 'Direktur',
        ]);

        $response = $this->actingAs($admin)->post(route('plans.store'), [
            'customer_id' => $customer->id,
            'plan_month' => now()->format('Y-m'),
            'planned_date' => now()->addDay()->format('Y-m-d'),
            'specific_objective' => 'Meeting koordinasi',
            'members' => [$dirut->id],
        ]);

        $response->assertSessionHasErrors(['members.0']);
        $this->assertEquals(
            'Pengguna yang dipilih tidak valid.',
            session('errors')->first('members.0')
        );
    }

    public function test_admin_users_page_still_displays_dirut(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
            'full_name' => 'Super Admin',
            'is_active' => true,
        ]);

        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'full_name' => 'Irwan Wardianto',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Irwan Wardianto');
    }
}
