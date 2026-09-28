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

class VisitReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_and_export_reports_to_excel_and_pdf()
    {
        $user = User::factory()->create([
            'role' => UserRole::TIM->value,
            'is_active' => true,
        ]);

        $area = Area::create(['area_code' => 'RYN01', 'area_name' => 'Rayon Jakarta', 'region' => 'DKI Jakarta']);
        $user->areas()->attach($area->id);

        $segment = Segment::create(['segment_code' => 'SEG01', 'segment_name' => 'Primkopad']);
        $customer = Customer::create([
    'customer_code' => 'CUST001',
    'customer_name' => 'Koperasi Primkopad Mabes AD',
    'area_id' => $area->id,
    'segment_id' => $segment->id,
    'owner_id' => $user->id,
    'address' => 'Jl. Mabes AD No. 1',
    'city' => 'Jakarta',
    'province' => 'DKI Jakarta',
    'contact_name' => 'Pak Budi',
    'contact_position' => 'Manager Pengadaan',
]);

        $plan = VisitPlan::create([
            'customer_id' => $customer->id,
            'area_id' => $area->id,
            'owner_id' => $user->id,
            'plan_month' => '2026-09',
            'planned_date' => '2026-09-23',
            'specific_objective' => 'Koordinasi pengadaan',
            'resource_notes' => 'Siapkan proposal',
        ]);

        // 1. Access report index with date range
        $response = $this->actingAs($user)->get(route('reports.index', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Rencana Kunjungan dan Realisasi Kunjungan');
        $response->assertSee('Koperasi Primkopad Mabes AD');

        // 2. Export Excel
        $excelResponse = $this->actingAs($user)->get(route('reports.exportExcel', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('content-type', 'application/vnd.ms-excel; charset=utf-8');

        // 3. Export PDF view
        $pdfResponse = $this->actingAs($user)->get(route('reports.exportPdf', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('Cetak / Download PDF');
    }
}
