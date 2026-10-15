<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Send push notification to a single user's registered devices.
     */
    public function sendToUser(User $user, string $title, string $body, ?string $url = null): bool
    {
        $subscriptions = $user->pushSubscriptions;
        if (!$subscriptions || $subscriptions->isEmpty()) {
            return false;
        }

        $webPush = $this->getWebPush();
        if (!$webPush) {
            Log::warning('[WebPush] VAPID keys missing or client unavailable.');
            return false;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?: url('/'),
        ]);

        $queued = 0;
        foreach ($subscriptions as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                ]);

                $webPush->queueNotification($subscription, $payload);
                $queued++;
            } catch (\Throwable $e) {
                Log::warning('[WebPush] Failed creating subscription instance: ' . $e->getMessage());
            }
        }

        if ($queued === 0) {
            return false;
        }

        return $this->flush($webPush);
    }

    /**
     * Send push notification to multiple users.
     */
    public function sendToUsers(iterable $users, string $title, string $body, ?string $url = null): int
    {
        $sentCount = 0;
        foreach ($users as $user) {
            if ($user instanceof User && $this->sendToUser($user, $title, $body, $url)) {
                $sentCount++;
            }
        }
        return $sentCount;
    }

    /**
     * Flush queued notifications and prune expired subscriptions.
     */
    protected function flush(WebPush $webPush): bool
    {
        try {
            $reports = $webPush->flush();
            $allSuccess = true;

            if (is_iterable($reports)) {
                foreach ($reports as $report) {
                    if (!$report->isSuccess()) {
                        $allSuccess = false;
                        Log::warning('[WebPush] Notification delivery failed: ' . $report->getReason());
                        if (method_exists($report, 'isSubscriptionExpired') && $report->isSubscriptionExpired()) {
                            PushSubscription::where('endpoint', (string) $report->getEndpoint())->delete();
                            Log::info('[WebPush] Pruned expired subscription: ' . $report->getEndpoint());
                        }
                    }
                }
            }

            return $allSuccess;
        } catch (\Throwable $e) {
            Log::error('[WebPush] Error flushing notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get or build WebPush client.
     */
    public function getWebPush(): ?WebPush
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

        try {
            return new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[WebPush] Error initializing WebPush client: ' . $e->getMessage());
            return null;
        }
    }
}
