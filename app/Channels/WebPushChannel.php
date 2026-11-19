<?php

namespace App\Channels;

use App\Models\PushSubscription;
use Illuminate\Notifications\Notification;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/** Sends a notification to every phone/browser the user turned alerts on for. Expired subscriptions are removed. */
class WebPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush') || ! config('webpush.public_key') || ! config('webpush.private_key')) {
            return;
        }
        $subs = PushSubscription::where('user_id', $notifiable->id)->get();
        if ($subs->isEmpty()) {
            return;
        }

        $push = new WebPush(['VAPID' => [
            'subject'    => config('webpush.subject'),
            'publicKey'  => config('webpush.public_key'),
            'privateKey' => config('webpush.private_key'),
        ]], ['TTL' => 6 * 3600, 'urgency' => 'high'], 20);

        $payload = json_encode($notification->toWebPush($notifiable));
        foreach ($subs as $s) {
            $push->queueNotification(Subscription::create([
                'endpoint' => $s->endpoint,
                'keys'     => ['p256dh' => $s->p256dh, 'auth' => $s->auth],
            ]), $payload);
        }

        foreach ($push->flush() as $report) {
            $hash = hash('sha256', $report->getEndpoint());
            if ($report->isSuccess()) {
                PushSubscription::where('endpoint_hash', $hash)->update(['last_used_at' => now()]);
            } elseif ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint_hash', $hash)->delete();
            } else {
                logger()->warning('Web push failed: ' . $report->getReason());
            }
        }
    }
}
