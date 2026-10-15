<?php

namespace App\Console\Commands;

use App\Enums\AgendaStatus;
use App\Enums\UserRole;
use App\Models\Agenda;
use App\Models\User;
use App\Services\WebPushService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAgendaMorningDigest extends Command
{
    protected $signature = 'agenda:morning-digest';
    protected $description = 'Send morning agenda digest notification to Direktur Utama';

    public function handle(WebPushService $webPushService): int
    {
        $todayStart = Carbon::now('Asia/Jakarta')->startOfDay();
        $todayEnd = Carbon::now('Asia/Jakarta')->endOfDay();

        $diruts = User::where('role', UserRole::DIREKTUR_UTAMA->value)
            ->where('is_active', true)
            ->get();

        if ($diruts->isEmpty()) {
            $this->info('No active Direktur Utama found.');
            return self::SUCCESS;
        }

        $processedCount = 0;

        foreach ($diruts as $dirut) {
            $agendas = Agenda::where('user_id', $dirut->id)
                ->where('status', AgendaStatus::TERJADWAL->value)
                ->whereBetween('start_time', [$todayStart, $todayEnd])
                ->orderBy('start_time', 'asc')
                ->get();

            if ($agendas->isEmpty()) {
                $this->info("No agendas today for {$dirut->display_name}.");
                continue;
            }

            $count = $agendas->count();
            $first = $agendas->first();
            $firstTime = $first->start_time->format('H:i');

            $title = "Agenda Hari Ini — {$dirut->display_name}";
            if ($count === 1) {
                $body = "1 Agenda Hari Ini: {$first->title} pukul {$firstTime} di " . ($first->location ?: 'lokasi belum ditentukan');
            } else {
                $body = "Ada {$count} agenda hari ini. Agenda pertama: {$first->title} pukul {$firstTime}";
            }

            $sent = $webPushService->sendToUser($dirut, $title, $body, route('dirut.dashboard'));
            if ($sent) {
                $processedCount++;
                $this->info("Sent morning digest to {$dirut->display_name} ({$count} agenda(s)).");
            } else {
                $this->warn("Could not send morning digest to {$dirut->display_name} (no active subscriptions or VAPID error).");
            }
        }

        return self::SUCCESS;
    }
}
