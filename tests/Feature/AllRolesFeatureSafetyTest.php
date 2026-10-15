<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Enums\AgendaStatus;
use App\Enums\AgendaType;
use App\Enums\DirectorInputStatus;
use App\Enums\DirectorInputType;
use App\Enums\FollowUpStatus;
use App\Enums\OutcomeType;
use App\Enums\PlanStatus;
use App\Enums\PriorityLevel;
use App\Enums\PriorityTier;
use App\Enums\SubmitStatus;
use App\Enums\UserRole;
use App\Models\Agenda;
use App\Models\AppSetting;
use App\Models\Area;
use App\Models\Customer;
use App\Models\DirectorInput;
use App\Models\FollowUp;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use App\Models\VisitReport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllRolesFeatureSafetyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $director;
    private User $tim;
    private User $dirut;
    private Area $area;
    private Segment $segment;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@pastac.co.id',
            'full_name' => 'Admin User',
            'role' => UserRole::ADMIN->value,
            'is_active' => true,
        ]);

        $this->director = User::factory()->create([
            'email' => 'director@pastac.co.id',
            'full_name' => 'Director User',
            'role' => UserRole::DIRECTOR->value,
            'is_active' => true,
        ]);

        $this->tim = User::factory()->create([
            'email' => 'tim@pastac.co.id',
            'full_name' => 'Sales Tim User',
            'role' => UserRole::TIM->value,
            'is_active' => true,
        ]);

        $this->dirut = User::factory()->create([
            'email' => 'dirut@pastac.co.id',
            'honorific' => 'Bpk',
            'full_name' => 'Irwan Wardianto',
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $this->area = Area::create([
            'area_code' => 'AREA-JKT',
            'area_name' => 'DKI Jakarta',
            'region' => 'Jawa Bagian Barat',
            'is_active' => true,
        ]);

        $this->segment = Segment::create([
            'segment_code' => 'SEG-MIL',
            'segment_name' => 'Militer & Pertahanan',
            'is_active' => true,
        ]);

        $this->tim->areas()->attach($this->area->id);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-001',
            'customer_name' => 'Koperasi Primkopad Mabes AD',
            'instansi' => 'TNI AD',
            'satuan' => 'Mabes AD',
            'area_id' => $this->area->id,
            'segment_id' => $this->segment->id,
            'owner_id' => $this->tim->id,
            'address' => 'Jl. Veteran No. 5',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'priority_tier' => 'A',
            'is_active' => true,
        ]);
    }

    /**
     * TEST SUITE 1: ADMIN ROLE CAPABILITIES & PERMISSIONS
     */
    public function test_admin_can_access_all_management_modules_and_sales(): void
    {
        // 1. Admin accesses dashboard & sales
        $this->actingAs($this->admin)->get('/home')->assertStatus(200);
        $this->actingAs($this->admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->admin)->get('/plans')->assertStatus(200);
        $this->actingAs($this->admin)->get('/reports')->assertStatus(200);
        $this->actingAs($this->admin)->get('/customers')->assertStatus(200);
        $this->actingAs($this->admin)->get('/followups')->assertStatus(200);
        $this->actingAs($this->admin)->get('/director-review')->assertStatus(200);

        // 2. Admin User Management CRUD
        $resUserIndex = $this->actingAs($this->admin)->get('/admin/users');
        $resUserIndex->assertStatus(200);

        $resCreateUser = $this->actingAs($this->admin)->post('/admin/users', [
            'email' => 'newtim@pastac.co.id',
            'full_name' => 'New Tim User',
            'role' => UserRole::TIM->value,
            'password' => 'password123',
            'is_active' => 1,
            'areas' => [$this->area->id],
        ]);
        $resCreateUser->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'newtim@pastac.co.id']);

        $newUser = User::where('email', 'newtim@pastac.co.id')->first();
        $this->actingAs($this->admin)->patch("/admin/users/{$newUser->id}/toggle")->assertSessionHasNoErrors();
        $this->assertFalse($newUser->fresh()->is_active);

        // 3. Admin Area Management CRUD
        $resCreateArea = $this->actingAs($this->admin)->post('/admin/areas', [
            'area_code' => 'AREA-SBY',
            'area_name' => 'Surabaya & Sekitarnya',
            'region' => 'Jawa Timur',
            'is_active' => 1,
        ]);
        $resCreateArea->assertSessionHasNoErrors();
        $this->assertDatabaseHas('areas', ['area_code' => 'AREA-SBY']);

        // 4. Admin Segment Management CRUD
        $resCreateSeg = $this->actingAs($this->admin)->post('/admin/segments', [
            'segment_code' => 'SEG-POL',
            'segment_name' => 'Kepolisian RI',
            'is_active' => 1,
        ]);
        $resCreateSeg->assertSessionHasNoErrors();
        $this->assertDatabaseHas('segments', ['segment_code' => 'SEG-POL']);

        // 5. Admin Settings & Audit Logs
        AppSetting::create(['setting_key' => 'COMPANY_NAME', 'setting_value' => 'PT Pastac']);
        $this->actingAs($this->admin)->get('/admin/settings')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/audit-logs')->assertStatus(200);
    }

    /**
     * TEST SUITE 2: DIRECTOR ROLE CAPABILITIES & PERMISSIONS
     */
    public function test_director_can_review_and_direct_but_cannot_access_admin_or_dirut(): void
    {
        // 1. Director can access sales & review
        $this->actingAs($this->director)->get('/home')->assertStatus(200);
        $this->actingAs($this->director)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->director)->get('/plans')->assertStatus(200);
        $this->actingAs($this->director)->get('/reports')->assertStatus(200);
        $this->actingAs($this->director)->get('/customers')->assertStatus(200);
        $this->actingAs($this->director)->get('/director-review')->assertStatus(200);

        // 2. Director creates a direction for Tim
        $resDir = $this->actingAs($this->director)->post('/director-review', [
            'customer_id' => $this->customer->id,
            'topic' => 'Kawal Tender Pengadaan Seragam',
            'input_type' => DirectorInputType::STRATEGIC_DIRECTION->value,
            'direction_text' => 'Segera koordinasikan dengan Kasi Logistik sebelum rapat pleno.',
            'assigned_to' => $this->tim->id,
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
            'priority' => PriorityLevel::HIGH->value,
        ]);
        $resDir->assertSessionHasNoErrors();
        $this->assertDatabaseHas('director_inputs', [
            'topic' => 'Kawal Tender Pengadaan Seragam',
            'assigned_to' => $this->tim->id,
        ]);

        // 3. Director is forbidden from Admin & Dirut routes
        $this->actingAs($this->director)->get('/admin/users')->assertStatus(403);
        $this->actingAs($this->director)->get('/admin/areas')->assertStatus(403);
        $this->actingAs($this->director)->get('/admin/segments')->assertStatus(403);
        $this->actingAs($this->director)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($this->director)->get('/admin/audit-logs')->assertStatus(403);
        $this->actingAs($this->director)->get('/dirut/dashboard')->assertStatus(403);
        $this->actingAs($this->director)->get('/dirut/agenda')->assertStatus(403);
    }

    /**
     * TEST SUITE 3: TIM (SALES) COMPLETE END-TO-END WORKFLOW
     */
    public function test_tim_complete_sales_lifecycle_plans_visit_report_followup_and_directions(): void
    {
        // 1. Tim creates a visit plan
        $plannedDate = Carbon::tomorrow()->format('Y-m-d');
        $planMonth = Carbon::tomorrow()->format('Y-m');

        $resPlan = $this->actingAs($this->tim)->post('/plans', [
            'customer_id' => $this->customer->id,
            'planned_date' => $plannedDate,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'activity_type' => ActivityType::SALES_VISIT->value,
            'priority' => PriorityLevel::HIGH->value,
            'monthly_objective' => 'Pengenalan produk',
            'specific_objective' => 'Audiensi dengan Karumkit',
        ]);
        $resPlan->assertSessionHasNoErrors();
        $this->assertDatabaseHas('visit_plans', [
            'customer_id' => $this->customer->id,
            'owner_id' => $this->tim->id,
            'planned_date' => $plannedDate,
        ]);

        $plan = VisitPlan::where('customer_id', $this->customer->id)->where('owner_id', $this->tim->id)->first();

        // 2. Tim starts the visit via My Week
        $this->actingAs($this->tim)->get('/my-week')->assertStatus(200);
        $this->actingAs($this->tim)->patch("/my-week/{$plan->id}/start")->assertSessionHasNoErrors();
        $this->assertEquals(PlanStatus::IN_PROGRESS, $plan->fresh()->status);

        // 3. Tim submits Visit Report with Follow-Up
        $resReport = $this->actingAs($this->tim)->post('/reports', [
            'visit_plan_id' => $plan->id,
            'actual_start_at' => Carbon::now()->subHours(2)->format('Y-m-d H:i:s'),
            'actual_end_at' => Carbon::now()->subHour()->format('Y-m-d H:i:s'),
            'outcome_summary' => 'Pertemuan berjalan lancar. Pihak koperasi tertarik dengan proposal.',
            'outcome_type' => OutcomeType::PROPOSAL->value,
            'engagement_score' => 5,
            'attendance_summary' => 'Dihadiri Ketua Primkopad dan Manager Keuangan.',
            'next_step_summary' => 'Kirimkan surat penawaran resmi besok pagi.',
            'follow_up_required' => 1,
            'action_title' => 'Kirim Surat Penawaran Resmi',
            'action_detail' => 'Lampirkan company profile dan katalog produk terbaru.',
            'owner_id' => $this->tim->id,
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
            'priority' => PriorityLevel::HIGH->value,
        ]);
        $resReport->assertSessionHasNoErrors();
        $this->assertEquals(PlanStatus::COMPLETED, $plan->fresh()->status);
        $this->assertDatabaseHas('visit_reports', ['visit_plan_id' => $plan->id]);
        $this->assertDatabaseHas('follow_ups', ['visit_plan_id' => $plan->id, 'action_title' => 'Kirim Surat Penawaran Resmi']);

        $followUp = FollowUp::where('visit_plan_id', $plan->id)->first();

        // 4. Tim updates Follow-Up to DONE
        $resDone = $this->actingAs($this->tim)->patch("/followups/{$followUp->id}/status", [
            'status' => FollowUpStatus::DONE->value,
            'completion_note' => 'Surat penawaran telah dikirimkan melalui kurir dan email.',
        ]);
        $resDone->assertSessionHasNoErrors();
        $this->assertEquals(FollowUpStatus::DONE, $followUp->fresh()->status);

        // 5. Tim acknowledges, starts, and completes a direction
        $dir = DirectorInput::create([
            'customer_id' => $this->customer->id,
            'area_id' => $this->area->id,
            'topic' => 'Follow up MOU Koperasi',
            'input_type' => DirectorInputType::STRATEGIC_DIRECTION->value,
            'direction_text' => 'Pastikan draft MOU siap sebelum akhir pekan.',
            'assigned_to' => $this->tim->id,
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
            'priority' => PriorityLevel::HIGH->value,
            'status' => DirectorInputStatus::OPEN->value,
            'created_by' => $this->director->id,
        ]);

        $this->actingAs($this->tim)->get('/my-directions')->assertStatus(200);
        $this->actingAs($this->tim)->patch("/my-directions/{$dir->id}/acknowledge")->assertSessionHasNoErrors();
        $this->assertEquals(DirectorInputStatus::ACKNOWLEDGED, $dir->fresh()->status);

        $this->actingAs($this->tim)->patch("/my-directions/{$dir->id}/start")->assertSessionHasNoErrors();
        $this->assertEquals(DirectorInputStatus::IN_PROGRESS, $dir->fresh()->status);

        $this->actingAs($this->tim)->patch("/my-directions/{$dir->id}/complete", [
            'completion_note' => 'Draft MOU telah disetujui legal.',
        ])->assertSessionHasNoErrors();
        $this->assertEquals(DirectorInputStatus::CLOSED, $dir->fresh()->status);

        // 6. Tim is forbidden from Admin & Director Review
        $this->actingAs($this->tim)->get('/director-review')->assertStatus(403);
        $this->actingAs($this->tim)->get('/admin/users')->assertStatus(403);
        $this->actingAs($this->tim)->get('/dirut/dashboard')->assertStatus(403);
        $this->actingAs($this->tim)->get('/dirut/agenda')->assertStatus(403);
    }

    /**
     * TEST SUITE 4: DIREKTUR UTAMA (DIRUT) MODULE ISOLATION & INTEGRITY
     */
    public function test_dirut_has_dedicated_agenda_and_is_fully_isolated_from_sales(): void
    {
        // 1. Dirut accessing /home redirects to /dirut/dashboard
        $this->actingAs($this->dirut)->get('/home')
            ->assertRedirect(route('dirut.dashboard'));

        // 2. Dirut accesses dedicated dashboard & calendar
        $resDash = $this->actingAs($this->dirut)->get('/dirut/dashboard');
        $resDash->assertStatus(200);
        $resDash->assertSee('Bpk Irwan Wardianto');

        $resCal = $this->actingAs($this->dirut)->get('/dirut/agenda');
        $resCal->assertStatus(200);
        $resCal->assertSee('Kalender Agenda');

        // 3. Dirut creates an Agenda
        $start = Carbon::now()->addDay()->setHour(10)->setMinute(0);
        $end = $start->copy()->addHour();

        $resAgenda = $this->actingAs($this->dirut)->post('/dirut/agenda', [
            'title' => 'Rapat Koordinasi Direksi',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s'),
            'location' => 'Ruang Rapat Utama',
            'description' => 'Evaluasi pencapaian Q3',
        ]);
        $resAgenda->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agendas', [
            'user_id' => $this->dirut->id,
            'title' => 'Rapat Koordinasi Direksi',
        ]);

        $agenda = Agenda::where('user_id', $this->dirut->id)->first();

        // 4. Dirut updates & deletes agenda
        $resUpdate = $this->actingAs($this->dirut)->put("/dirut/agenda/{$agenda->id}", [
            'title' => 'Rapat Koordinasi Direksi (Revisi)',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s'),
            'status' => AgendaStatus::SELESAI->value,
        ]);
        $resUpdate->assertSessionHasNoErrors();
        $this->assertEquals(AgendaStatus::SELESAI, $agenda->fresh()->status);

        $resDel = $this->actingAs($this->dirut)->delete("/dirut/agenda/{$agenda->id}");
        $resDel->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);

        // 5. Dirut accessing any sales route redirects to /dirut/dashboard
        $salesRoutes = ['/plans', '/my-week', '/reports', '/customers', '/followups', '/my-directions', '/dashboard', '/director-review', '/admin/users'];
        foreach ($salesRoutes as $route) {
            $this->actingAs($this->dirut)->get($route)
                ->assertRedirect(route('dirut.dashboard'));
        }
    }
}
