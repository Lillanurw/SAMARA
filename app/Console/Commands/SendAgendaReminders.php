<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agenda;
use App\Models\PushSubscription;
use App\Enums\AgendaStatus;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class SendAgendaReminders extends Command
{
    protected $signature = 'agenda:remind';
    protected $description = 'Send web push notifications to Direktur Utama for agendas starting in 3 hours';

    public function handle()
    {
        $now = now();
        $targetTime = $now->copy()->addHours(3);

        $agendas = Agenda::where('status', AgendaStatus::TERJADWAL)
            ->whereNull('reminder_sent_at')
            ->where('start_time', '>=', $now)
            ->where('start_time', '<=', $targetTime)
            ->get();

        if ($agendas->isEmpty()) {
            $this->info('No upcoming agendas to remind.');
            return self::SUCCESS;
        }

        $webPush = $this->getWebPush();
        if (!$webPush) {
            $this->warn('WebPush client could not be initialized (missing VAPID keys).');
            return self::FAILURE;
        }

        $hasQueued = false;

        foreach ($agendas as $agenda) {
            $user = $agenda->user;
            if ($user) {
                $subs = $user->pushSubscriptions;
                if ($subs && $subs->isNotEmpty()) {
                    $userName = $user->display_name;
                    $payload = json_encode([
                        'title' => "Agenda {$userName} dalam 3 jam",
                        'body' => "Agenda {$userName}: " . $agenda->title . "\nPukul " . $agenda->start_time->format('H:i') . " · " . ($agenda->location ?? 'Tidak ada lokasi'),
                        'url' => route('dirut.dashboard')
                    ]);

                    foreach ($subs as $sub) {
                        $subscription = Subscription::create([
                            'endpoint' => $sub->endpoint,
                            'publicKey' => $sub->public_key,
                            'authToken' => $sub->auth_token,
                        ]);

                        $webPush->queueNotification($subscription, $payload);
                        $hasQueued = true;
                    }
                }
            }

            $agenda->update(['reminder_sent_at' => now()]);
        }

        if ($hasQueued) {
            $reports = $webPush->flush();
            if (is_iterable($reports)) {
                foreach ($reports as $report) {
                    if (!$report->isSuccess()) {
                        Log::error('Push Notification failed: ' . $report->getReason());
                        if (method_exists($report, 'isSubscriptionExpired') && $report->isSubscriptionExpired()) {
                            PushSubscription::where('endpoint', (string) $report->getEndpoint())->delete();
                        }
                    }
                }
            }
        }

        $this->info("Processed reminders for {$agendas->count()} agenda(s).");
        return self::SUCCESS;
    }

    protected function getWebPush(): ?WebPush
    {
        if (app()->bound(WebPush::class)) {
            return app(WebPush::class);
        }

        $publicKey = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $subject = config('services.vapid.subject') ?: 'mailto:admin@pastac.co.id';

        if (empty($publicKey) || empty($privateKey)) {
            return null;
        }

        return new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);
    }
}
