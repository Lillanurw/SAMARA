<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Enums\PriorityLevel;
use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_visit_plan_and_double_submit_is_prevented(): void
    {
        $user = User::factory()->create(['role' => UserRole::TIM->value, 'is_active' => true]);
        $area = Area::create(['area_code' => 'A1', 'area_name' => 'Area 1', 'region' => 'R1']);
        $segment = Segment::create(['segment_code' => 'S1', 'segment_name' => 'Seg 1']);
        $customer = Customer::create([
            'customer_code' => 'C1',
            'customer_name' => 'Cust 1',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $user->id,
            'address' => 'Addr',
            'city' => 'City',
            'province' => 'Prov',
            'priority_tier' => 'B',
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'planned_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'activity_type' => ActivityType::SALES_VISIT->value,
            'priority' => PriorityLevel::HIGH->value,
            'monthly_objective' => 'Monthly Obj',
            'specific_objective' => 'Specific Obj',
        ];

        // 1. First submission succeeds
        $response1 = $this->actingAs($user)->post('/plans', $payload);
        $response1->assertSessionHasNoErrors();
        $this->assertDatabaseHas('visit_plans', ['customer_id' => $customer->id, 'owner_id' => $user->id]);

        // 2. Second submission with exact same date/time/customer fails double-submit
        $response2 = $this->actingAs($user)->post('/plans', $payload);
        $response2->assertSessionHasErrors('customer_id');
    }

    public function test_user_can_update_and_delete_visit_plan(): void
    {
        $user = User::factory()->create(['role' => UserRole::TIM->value, 'is_active' => true]);
        $area = Area::create(['area_code' => 'A1', 'area_name' => 'Area 1', 'region' => 'R1']);
        $segment = Segment::create(['segment_code' => 'S1', 'segment_name' => 'Seg 1']);
        $customer = Customer::create([
            'customer_code' => 'C1',
            'customer_name' => 'Cust 1',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $user->id,
            'address' => 'Addr',
            'city' => 'City',
            'province' => 'Prov',
            'priority_tier' => 'B',
        ]);

        $plan = VisitPlan::create([
            'plan_number' => 'VP-TEST-001',
            'customer_id' => $customer->id,
            'area_id' => $area->id,
            'owner_id' => $user->id,
            'plan_month' => now()->format('Y-m'),
            'planned_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'activity_type' => ActivityType::SALES_VISIT->value,
            'priority' => PriorityLevel::MEDIUM->value,
            'monthly_objective' => 'Old Obj',
            'specific_objective' => 'Old Specific',
            'status' => 'PLANNED',
            'created_by' => $user->id,
        ]);

        // Update
        $updatePayload = [
            'customer_id' => $customer->id,
            'planned_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'activity_type' => ActivityType::PRODUCT_DEMO->value,
            'priority' => PriorityLevel::CRITICAL->value,
            'monthly_objective' => 'Updated Obj',
            'specific_objective' => 'Updated Specific',
        ];

        $response = $this->actingAs($user)->put("/plans/{$plan->id}", $updatePayload);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('visit_plans', ['id' => $plan->id, 'monthly_objective' => 'Updated Obj']);

        // Delete
        $deleteResponse = $this->actingAs($user)->delete("/plans/{$plan->id}");
        $deleteResponse->assertSessionHasNoErrors();
        $this->assertSoftDeleted('visit_plans', ['id' => $plan->id]);
    }
}
