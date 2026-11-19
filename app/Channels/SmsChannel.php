<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Sends SMS through Africa's Talking when AT_API_KEY is configured. */
class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $cfg = config('noc.sms');
        $to = $notifiable->routeNotificationFor('sms', $notification);
        if (! $cfg['enabled'] || ! $to || ! method_exists($notification, 'toSms')) {
            return;
        }

        $host = $cfg['username'] === 'sandbox' ? 'https://api.sandbox.africastalking.com' : 'https://api.africastalking.com';
        $payload = ['username' => $cfg['username'], 'to' => '+'.$to, 'message' => $notification->toSms($notifiable)];
        if ($cfg['sender_id']) {
            $payload['from'] = $cfg['sender_id'];
        }

        try {
            Http::asForm()->acceptJson()->withHeaders(['apiKey' => $cfg['api_key']])
                ->timeout(15)->post($host.'/version1/messaging', $payload)->throw();
        } catch (\Throwable $e) {
            Log::warning('SMS send failed: '.$e->getMessage());
        }
    }
}
