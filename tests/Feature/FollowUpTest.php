<?php

namespace Tests\Feature;

use App\Enums\FollowUpStatus;
use App\Enums\PriorityLevel;
use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_done_status_requires_completion_note_and_blocked_requires_blocked_reason(): void
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
            'address' => 'Addr', 'city' => 'City', 'province' => 'Prov', 'priority_tier' => 'B',
        ]);
        $plan = VisitPlan::create([
            'plan_number' => 'VP-TEST-01',
            'customer_id' => $customer->id,
            'area_id' => $area->id,
            'owner_id' => $user->id,
            'plan_month' => now()->format('Y-m'),
            'planned_date' => now()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'monthly_objective' => 'Obj',
            'specific_objective' => 'Obj',
        ]);

        $fu = FollowUp::create([
            'action_number' => 'ACT-TEST-01',
            'visit_plan_id' => $plan->id,
            'customer_id' => $customer->id,
            'action_title' => 'Sample Action',
            'action_detail' => 'Detail',
            'owner_id' => $user->id,
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => FollowUpStatus::OPEN->value,
        ]);

        // 1. Updating status to DONE without completion_note fails
        $resDoneFail = $this->actingAs($user)->patch("/followups/{$fu->id}/status", [
            'status' => 'DONE',
        ]);
        $resDoneFail->assertSessionHasErrors('completion_note');

        // 2. Updating status to DONE with completion_note succeeds
        $resDoneSuccess = $this->actingAs($user)->patch("/followups/{$fu->id}/status", [
            'status' => 'DONE',
            'completion_note' => 'Sudah dikirimkan via email',
        ]);
        $resDoneSuccess->assertSessionHasNoErrors();
        $fu->refresh();
        $this->assertEquals(FollowUpStatus::DONE, $fu->status);
    }
}
