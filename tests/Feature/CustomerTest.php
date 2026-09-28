<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_area_segment_and_customer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value, 'is_active' => true]);
        $owner = User::factory()->create(['role' => UserRole::TIM->value, 'is_active' => true]);

        $area = Area::create([
            'area_code' => 'TEST-AREA',
            'area_name' => 'Area Test',
            'region' => 'Region Test',
            'is_active' => true,
        ]);

        $segment = Segment::create([
            'segment_code' => 'TEST-SEG',
            'segment_name' => 'Segment Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/customers', [
            'customer_code' => 'CUST-TEST-01',
            'customer_name' => 'Customer Primkopad Test',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $owner->id,
            'address' => 'Jl. Test No. 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'priority_tier' => 'A',
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customers', ['customer_code' => 'CUST-TEST-01']);
    }
}
