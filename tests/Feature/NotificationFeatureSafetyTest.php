<?php

namespace Tests\Feature;

use App\Enums\ActivityType;
use App\Enums\AgendaStatus;
use App\Enums\AgendaType;
use App\Enums\DirectorInputType;
use App\Enums\OutcomeType;
use App\Enums\PriorityLevel;
use App\Enums\PriorityTier;
use App\Enums\UserRole;
use App\Models\Agenda;
use App\Models\Area;
use App\Models\Customer;
use App\Models\PushSubscription;
use App\Models\Segment;
use App\Models\User;
use App\Models\VisitPlan;
use App\Notifications\DirectorInputCreatedNotification;
use App\Notifications\ReportFollowUpRequiredNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationFeatureSafetyTest extends TestCase
{
    use RefreshDatabase;

    private User $director;
    private User $tim;
    private User $dirut;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->director = User::factory()->create([
            'email' => 'Ghaffari@pastac.co.id',
            'full_name' => 'Direktur SMD - Muhammad Ghaffari (MG)',
            'role' => UserRole::DIRECTOR->value,
            'is_active' => true,
        ]);

        $this->tim = User::factory()->create([
            'email' => 'tim@pastac.co.id',
            'full_name' => 'Sales Officer',
            'role' => UserRole::TIM->value,
            'is_active' => true,
        ]);

        $this->dirut = User::factory()->create([
            'email' => 'dirut@pastac.co.id',
            'full_name' => 'Irwan Wardianto',
            'honorific' => 'Bpk',
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $area = Area::create([
            'area_code' => 'RAYON-1',
            'area_name' => 'Rayon 1',
            'region' => 'Jawa Bagian Barat',
            'is_active' => true,
        ]);
        $segment = Segment::create([
            'segment_code' => 'RS',
            'segment_name' => 'Rumah Sakit',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-0099',
            'customer_name' => 'RS Militer Kartika',
            'instansi' => 'TNI AD',
            'satuan' => 'Mabes AD',
            'area_id' => $area->id,
            'segment_id' => $segment->id,
            'owner_id' => $this->tim->id,
            'address' => 'Jl. RS Militer No. 10',
            'city' => 'Jakarta Timur',
            'province' => 'DKI Jakarta',
            'priority_tier' => 'A',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Push subscription endpoint stores mobile subscription for all roles (Director, Tim, Dirut).
     */
    public function test_all_roles_can_register_push_subscription(): void
    {
        foreach ([$this->director, $this->tim, $this->dirut] as $user) {
            $response = $this->actingAs($user)->postJson('/push-subscriptions', [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/' . $user->id,
                'keys' => [
                    'p256dh' => 'mock-p256dh-key-' . $user->id,
                    'auth' => 'mock-auth-token-' . $user->id,
                ],
            ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('push_subscriptions', [
                'user_id' => $user->id,
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/' . $user->id,
            ]);
        }
    }

    /**
     * Test 2: Director (Pak Ghaffari) receives notification when report needs follow up.
     */
    public function test_director_receives_notification_when_report_needs_follow_up(): void
    {
        $plan = VisitPlan::create([
            'customer_id' => $this->customer->id,
            'owner_id' => $this->tim->id,
            'area_id' => $this->customer->area_id,
            'planned_date' => Carbon::now()->format('Y-m-d'),
            'plan_month' => Carbon::now()->format('Y-m'),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'activity_type' => ActivityType::SALES_VISIT->value,
            'priority' => PriorityLevel::HIGH->value,
            'monthly_objective' => 'Negosiasi harga',
            'specific_objective' => 'Closing PO seragam medis',
        ]);

        $res = $this->actingAs($this->tim)->post('/reports', [
            'visit_plan_id' => $plan->id,
            'actual_start_at' => Carbon::now()->subHours(2)->format('Y-m-d H:i:s'),
            'actual_end_at' => Carbon::now()->subHour()->format('Y-m-d H:i:s'),
            'outcome_summary' => 'Rumah sakit membutuhkan revisi spesifikasi teknis.',
            'outcome_type' => OutcomeType::PROPOSAL->value,
            'engagement_score' => 4,
            'attendance_summary' => 'Dihadiri Karumkit dan Kasi Logistik',
            'next_step_summary' => 'Revisi penawaran harga sesuai spek baru.',
            'follow_up_required' => 1,
            'action_title' => 'Kirim Revisi Penawaran Spek Baru',
            'action_detail' => 'Penyesuaian gramasi kain medis antibakteri.',
            'owner_id' => $this->tim->id,
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
            'priority' => PriorityLevel::HIGH->value,
        ]);

        $res->assertSessionHasNoErrors();

        // Verify director received database notification
        $directorNotif = $this->director->fresh()->notifications()->first();
        $this->assertNotNull($directorNotif);
        $this->assertEquals('Laporan Butuh Tindak Lanjut', $directorNotif->data['title']);
        $this->assertEquals('Kirim Revisi Penawaran Spek Baru', $directorNotif->data['action_title']);
    }

    /**
     * Test 3: Tim receives notification when Director (Pak Ghaffari) creates a directive.
     */
    public function test_tim_receives_notification_when_director_gives_direction(): void
    {
        $res = $this->actingAs($this->director)->post('/director-review', [
            'customer_id' => $this->customer->id,
            'topic' => 'Kawal Finalisasi PO Rumah Sakit',
            'input_type' => DirectorInputType::STRATEGIC_DIRECTION->value,
            'direction_text' => 'Pastikan sampel kain diserahkan langsung ke Karumkit hari ini.',
            'assigned_to' => $this->tim->id,
            'due_date' => Carbon::tomorrow()->format('Y-m-d'),
            'priority' => PriorityLevel::HIGH->value,
        ]);

        $res->assertSessionHasNoErrors();

        // Verify Tim received database notification
        $timNotif = $this->tim->fresh()->notifications()->first();
        $this->assertNotNull($timNotif);
        $this->assertEquals('Arahan/Saran Director Baru', $timNotif->data['title']);
        $this->assertStringContainsString('Kawal Finalisasi PO Rumah Sakit', $timNotif->data['message']);
    }

    /**
     * Test 4: Dirut receives morning agenda digest notification via artisan command.
     */
    public function test_dirut_receives_morning_agenda_digest(): void
    {
        // 1. Create agenda today for Dirut
        Agenda::create([
            'user_id' => $this->dirut->id,
            'title' => 'Rapat Pleno Direksi Bulanan',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => Carbon::today('Asia/Jakarta')->setHour(10)->setMinute(0),
            'end_time' => Carbon::today('Asia/Jakarta')->setHour(12)->setMinute(0),
            'location' => 'Board Room Lt. 3',
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // 2. Add push subscription for Dirut
        PushSubscription::create([
            'user_id' => $this->dirut->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/dirut-device',
            'public_key' => 'mock-dirut-key',
            'auth_token' => 'mock-dirut-token',
        ]);

        // Mock WebPush so no network calls are performed
        $mockWebPush = \Mockery::mock(\Minishlink\WebPush\WebPush::class);
        $mockWebPush->shouldReceive('queueNotification')->once();
        $mockWebPush->shouldReceive('flush')->once()->andReturn((function () {
            yield from [];
        })());
        $this->app->instance(\Minishlink\WebPush\WebPush::class, $mockWebPush);

        // 3. Run morning-digest command
        $this->artisan('agenda:morning-digest')->assertSuccessful();
    }
}
