<?php

namespace Tests\Feature;

use App\Enums\AgendaStatus;
use App\Enums\AgendaType;
use App\Enums\UserRole;
use App\Models\Agenda;
use App\Models\PushSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AgendaDirutTest extends TestCase
{
    use RefreshDatabase;

    public function test_dirut_accessing_home_redirects_to_dirut_dashboard(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($dirut)->get('/home');

        $response->assertRedirect(route('dirut.dashboard'));
    }

    public function test_tim_user_accessing_dirut_agenda_receives_403(): void
    {
        $tim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($tim)->get('/dirut/agenda');

        $response->assertStatus(403);
    }

    public function test_dirut_cannot_update_or_delete_other_users_agenda(): void
    {
        $dirut1 = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);
        $dirut2 = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $agendaDirut2 = Agenda::create([
            'user_id' => $dirut2->id,
            'title' => 'Agenda Rahasia Dirut 2',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(2),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // Attempt update by dirut1
        $updateResponse = $this->actingAs($dirut1)->put("/dirut/agenda/{$agendaDirut2->id}", [
            'title' => 'Hacked Agenda',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addDays(2)->toDateTimeString(),
            'end_time' => now()->addDays(2)->addHours(2)->toDateTimeString(),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);
        $updateResponse->assertStatus(403);

        // Attempt delete by dirut1
        $deleteResponse = $this->actingAs($dirut1)->delete("/dirut/agenda/{$agendaDirut2->id}");
        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('agendas', [
            'id' => $agendaDirut2->id,
            'title' => 'Agenda Rahasia Dirut 2',
        ]);
    }

    public function test_agenda_validation_rejects_invalid_type_and_status(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        // Store with invalid type
        $responseStore = $this->actingAs($dirut)->post('/dirut/agenda', [
            'title' => 'Agenda Baru',
            'type' => 'TIPE_PALSU',
            'start_time' => now()->addDays(1)->toDateTimeString(),
            'end_time' => now()->addDays(1)->addHours(1)->toDateTimeString(),
        ]);
        $responseStore->assertSessionHasErrors('type');

        // Valid store
        $validStore = $this->actingAs($dirut)->post('/dirut/agenda', [
            'title' => 'Agenda Valid',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addDays(1)->toDateTimeString(),
            'end_time' => now()->addDays(1)->addHours(1)->toDateTimeString(),
            'description' => 'Diskusi internal bisnis',
        ]);
        $validStore->assertRedirect(route('agenda.index'));

        $agenda = Agenda::where('title', 'Agenda Valid')->firstOrFail();

        // Update with invalid status
        $responseUpdate = $this->actingAs($dirut)->put("/dirut/agenda/{$agenda->id}", [
            'title' => 'Agenda Valid',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => $agenda->start_time->toDateTimeString(),
            'end_time' => $agenda->end_time->toDateTimeString(),
            'status' => 'STATUS_PALSU',
        ]);
        $responseUpdate->assertSessionHasErrors('status');

        // Test start_time change resets reminder_sent_at
        $agenda->update(['reminder_sent_at' => now()]);
        $this->assertNotNull($agenda->fresh()->reminder_sent_at);

        $newStartTime = now()->addDays(3);
        $this->actingAs($dirut)->put("/dirut/agenda/{$agenda->id}", [
            'title' => 'Agenda Diubah Waktunya',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => $newStartTime->toDateTimeString(),
            'end_time' => $newStartTime->copy()->addHours(2)->toDateTimeString(),
            'status' => AgendaStatus::TERJADWAL->value,
        ])->assertRedirect(route('agenda.index'));

        $this->assertNull($agenda->fresh()->reminder_sent_at);
    }

    public function test_agenda_remind_command_sends_notification_and_sets_reminder_sent_at(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $subscription = PushSubscription::create([
            'user_id' => $dirut->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-123',
            'public_key' => 'fake_public_key',
            'auth_token' => 'fake_auth_token',
        ]);

        // Agenda starting in 2 hours (within 3-hour window)
        $agendaIn2Hours = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Rapat Direksi Mendesak',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(3),
            'status' => AgendaStatus::TERJADWAL->value,
            'reminder_sent_at' => null,
        ]);

        // Mock WebPush so no network calls are performed
        $mockWebPush = Mockery::mock(\Minishlink\WebPush\WebPush::class);
        $mockWebPush->shouldReceive('queueNotification')
            ->once()
            ->with(
                Mockery::on(fn($sub) => $sub->getEndpoint() === $subscription->endpoint),
                Mockery::on(fn($payload) => str_contains($payload, 'Rapat Direksi Mendesak'))
            );
        $mockWebPush->shouldReceive('flush')
            ->once()
            ->andReturn((function () {
                yield from [];
            })());

        $this->app->instance(\Minishlink\WebPush\WebPush::class, $mockWebPush);

        $this->artisan('agenda:remind')->assertSuccessful();

        $this->assertNotNull($agendaIn2Hours->fresh()->reminder_sent_at);
    }

    public function test_agenda_remind_command_ignores_past_future_and_reminded_agendas(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        // Past agenda
        $pastAgenda = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Agenda Masa Lalu',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->subHour(),
            'end_time' => now()->subMinutes(10),
            'status' => AgendaStatus::TERJADWAL->value,
            'reminder_sent_at' => null,
        ]);

        // Far future agenda (> 3 hours)
        $futureAgenda = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Agenda 5 Jam Lagi',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addHours(5),
            'end_time' => now()->addHours(6),
            'status' => AgendaStatus::TERJADWAL->value,
            'reminder_sent_at' => null,
        ]);

        // Already reminded agenda
        $alreadyRemindedTime = now()->subMinutes(30);
        $remindedAgenda = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Agenda Sudah Diingatkan',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(3),
            'status' => AgendaStatus::TERJADWAL->value,
            'reminder_sent_at' => $alreadyRemindedTime,
        ]);

        // Mock WebPush that should NOT queue any notification
        $mockWebPush = Mockery::mock(\Minishlink\WebPush\WebPush::class);
        $mockWebPush->shouldReceive('queueNotification')->never();
        $this->app->instance(\Minishlink\WebPush\WebPush::class, $mockWebPush);

        $this->artisan('agenda:remind')->assertSuccessful();

        $this->assertNull($pastAgenda->fresh()->reminder_sent_at);
        $this->assertNull($futureAgenda->fresh()->reminder_sent_at);
        $this->assertEquals(
            $alreadyRemindedTime->timestamp,
            $remindedAgenda->fresh()->reminder_sent_at->timestamp
        );
    }

    public function test_agenda_remind_deletes_expired_push_subscriptions(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $expiredSub = PushSubscription::create([
            'user_id' => $dirut->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/expired-endpoint',
            'public_key' => 'fake_public_key',
            'auth_token' => 'fake_auth_token',
        ]);

        Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Rapat Evaluasi',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
            'status' => AgendaStatus::TERJADWAL->value,
            'reminder_sent_at' => null,
        ]);

        // Mock report that indicates expired subscription
        $reportMock = Mockery::mock(\Minishlink\WebPush\MessageSentReport::class);
        $reportMock->shouldReceive('isSuccess')->andReturn(false);
        $reportMock->shouldReceive('getReason')->andReturn('Subscription expired / 410 Gone');
        $reportMock->shouldReceive('isSubscriptionExpired')->andReturn(true);
        $reportMock->shouldReceive('getEndpoint')->andReturn($expiredSub->endpoint);

        $mockWebPush = Mockery::mock(\Minishlink\WebPush\WebPush::class);
        $mockWebPush->shouldReceive('queueNotification')->once();
        $mockWebPush->shouldReceive('flush')
            ->once()
            ->andReturn((function () use ($reportMock) {
                yield $reportMock;
            })());

        $this->app->instance(\Minishlink\WebPush\WebPush::class, $mockWebPush);

        $this->artisan('agenda:remind')->assertSuccessful();

        $this->assertDatabaseMissing('push_subscriptions', [
            'id' => $expiredSub->id,
        ]);
    }

    public function test_dirut_accessing_sales_routes_redirects_to_dirut_dashboard(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $salesUrls = [
            '/plans',
            '/my-week',
            '/reports',
            '/customers',
            '/followups',
            '/dashboard',
            '/my-directions',
        ];

        foreach ($salesUrls as $url) {
            $response = $this->actingAs($dirut)->get($url);
            $response->assertRedirect(route('dirut.dashboard'));
            $response->assertSessionHas('error', 'Halaman ini tidak tersedia untuk Direktur Utama.');
        }
    }

    public function test_dirut_dashboard_does_not_contain_sales_menu_labels(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($dirut)->get('/dirut/dashboard');
        $response->assertStatus(200);

        $response->assertDontSeeText('Monthly Plan');
        $response->assertDontSeeText('My Week');
        $response->assertDontSeeText('Customers');
        $response->assertDontSeeText('Follow-ups');
        $response->assertDontSeeText('Dashboard Analitik');
        $response->assertDontSeeText('Arahan untuk Saya');
        $response->assertDontSeeText('Arahan Saya');
        $response->assertSeeText('+ Tambah Agenda');
        $response->assertSeeText('Lihat Kalender');
    }

    public function test_home_for_tim_does_not_contain_kalender_agenda(): void
    {
        $tim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($tim)->get('/home');
        $response->assertStatus(200);

        $response->assertDontSeeText('Kalender Agenda');
        $response->assertDontSeeText('Dirut Dashboard');
    }

    public function test_agenda_view_calendar_and_list_accessible_by_dirut_and_forbidden_for_tim(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);
        $tim = User::factory()->create([
            'role' => UserRole::TIM->value,
            'is_active' => true,
        ]);

        // Dirut - Kalender
        $resKalenderDirut = $this->actingAs($dirut)->get('/dirut/agenda?view=kalender');
        $resKalenderDirut->assertStatus(200);
        $resKalenderDirut->assertSeeText('Kalender Agenda');

        // Dirut - Daftar
        $resDaftarDirut = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar');
        $resDaftarDirut->assertStatus(200);
        $resDaftarDirut->assertSeeText('Kalender Agenda');

        // TIM - Kalender
        $resKalenderTim = $this->actingAs($tim)->get('/dirut/agenda?view=kalender');
        $resKalenderTim->assertStatus(403);

        // TIM - Daftar
        $resDaftarTim = $this->actingAs($tim)->get('/dirut/agenda?view=daftar');
        $resDaftarTim->assertStatus(403);
    }

    public function test_dirut_does_not_see_other_users_agendas_in_list_and_calendar(): void
    {
        $dirut1 = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);
        $dirut2 = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        Agenda::create([
            'user_id' => $dirut1->id,
            'title' => 'Agenda Kepunyaan Dirut 1',
            'type' => AgendaType::RAPAT_INTERNAL->value,
            'start_time' => now()->startOfWeek(Carbon::MONDAY)->addHours(10),
            'end_time' => now()->startOfWeek(Carbon::MONDAY)->addHours(11),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        Agenda::create([
            'user_id' => $dirut2->id,
            'title' => 'Agenda Rahasia Dirut 2',
            'type' => AgendaType::UNDANGAN_KEDINASAN->value,
            'start_time' => now()->startOfWeek(Carbon::MONDAY)->addHours(10),
            'end_time' => now()->startOfWeek(Carbon::MONDAY)->addHours(11),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // Tab Daftar
        $resDaftar = $this->actingAs($dirut1)->get('/dirut/agenda?view=daftar&range=minggu_ini');
        $resDaftar->assertStatus(200);
        $resDaftar->assertSeeText('Agenda Kepunyaan Dirut 1');
        $resDaftar->assertDontSeeText('Agenda Rahasia Dirut 2');

        // Tab Kalender
        $resKalender = $this->actingAs($dirut1)->get('/dirut/agenda?view=kalender');
        $resKalender->assertStatus(200);
        $resKalender->assertSee('Agenda Kepunyaan Dirut 1');
        $resKalender->assertDontSee('Agenda Rahasia Dirut 2');
    }

    public function test_custom_range_with_to_before_from_fails_validation(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $response = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar&range=kustom&from=2026-10-20&to=2026-10-15');
        $response->assertSessionHasErrors('to');
    }

    public function test_agenda_range_filters_and_boundary_crossing_events(): void
    {
        $dirut = User::factory()->create([
            'role' => UserRole::DIREKTUR_UTAMA->value,
            'is_active' => true,
        ]);

        $mondayThisWeek = now()->startOfWeek(Carbon::MONDAY);
        $mondayNextWeek = now()->addWeek()->startOfWeek(Carbon::MONDAY);

        // Event A: Dimulai sebelum minggu ini (Sabtu minggu lalu), selesai Selasa minggu ini
        $eventA = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Event Spanning Before To This Week',
            'type' => AgendaType::KUNJUNGAN_PERJALANAN_DINAS->value,
            'start_time' => $mondayThisWeek->copy()->subDays(2)->setTime(10, 0),
            'end_time' => $mondayThisWeek->copy()->addDays(1)->setTime(12, 0),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // Event B: Dimulai Sabtu minggu ini, selesai Selasa minggu depan (melintasi pergantian minggu)
        $eventB = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Event Spanning This Week To Next Week',
            'type' => AgendaType::PERTEMUAN_EKSTERNAL->value,
            'start_time' => $mondayThisWeek->copy()->addDays(5)->setTime(8, 0), // Sabtu
            'end_time' => $mondayNextWeek->copy()->addDays(1)->setTime(17, 0), // Selasa depan
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // Event C: Berada sepenuhnya di minggu depan (Rabu - Kamis minggu depan)
        $eventC = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Event Only In Next Week',
            'type' => AgendaType::PENDIDIKAN_PENGEMBANGAN_DIRI->value,
            'start_time' => $mondayNextWeek->copy()->addDays(2)->setTime(9, 0),
            'end_time' => $mondayNextWeek->copy()->addDays(3)->setTime(17, 0),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // Event D: Melintasi batas bulan ini
        $firstDayThisMonth = now()->startOfMonth();
        $eventD = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Event Spanning Month Boundary',
            'type' => AgendaType::UNDANGAN_KEDINASAN->value,
            'start_time' => $firstDayThisMonth->copy()->subDay()->setTime(20, 0),
            'end_time' => $firstDayThisMonth->copy()->addDays(2)->setTime(10, 0),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // Event E: Kustom di tanggal khusus (mis. Desember 2026)
        $eventE = Agenda::create([
            'user_id' => $dirut->id,
            'title' => 'Event In Custom Date Range',
            'type' => AgendaType::AGENDA_PRIBADI->value,
            'start_time' => Carbon::parse('2026-12-08 10:00:00'),
            'end_time' => Carbon::parse('2026-12-12 18:00:00'),
            'status' => AgendaStatus::TERJADWAL->value,
        ]);

        // 1. Test Filter: minggu_ini
        $resMingguIni = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar&range=minggu_ini');
        $resMingguIni->assertStatus(200);
        $resMingguIni->assertSeeText($eventA->title); // Masuk karena selesai di minggu ini
        $resMingguIni->assertSeeText($eventB->title); // Masuk karena mulai di minggu ini
        $resMingguIni->assertDontSeeText($eventC->title); // Di luar minggu ini

        // 2. Test Filter: minggu_depan
        $resMingguDepan = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar&range=minggu_depan');
        $resMingguDepan->assertStatus(200);
        $resMingguDepan->assertDontSeeText($eventA->title); // Sudah selesai minggu lalu
        $resMingguDepan->assertSeeText($eventB->title); // Masuk karena beririsan sampai Selasa minggu depan
        $resMingguDepan->assertSeeText($eventC->title); // Masuk karena di minggu depan

        // 3. Test Filter: bulan_ini
        $resBulanIni = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar&range=bulan_ini');
        $resBulanIni->assertStatus(200);
        $resBulanIni->assertSeeText($eventD->title); // Masuk karena melintasi awal bulan ini

        // 4. Test Filter: kustom (2026-12-10 s/d 2026-12-15)
        // Event E (08-12 Des) beririsan karena end_time 12 Des >= from 10 Des dan start_time 08 Des <= to 15 Des
        $resKustom = $this->actingAs($dirut)->get('/dirut/agenda?view=daftar&range=kustom&from=2026-12-10&to=2026-12-15');
        $resKustom->assertStatus(200);
        $resKustom->assertSeeText($eventE->title);
        $resKustom->assertDontSeeText($eventA->title);
    }
}
