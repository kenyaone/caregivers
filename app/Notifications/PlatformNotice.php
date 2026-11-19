<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * One notification type for all platform events: stored in-app,
 * emailed, and (optionally) sent by SMS.
 */
class PlatformNotice extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public bool $sms = false,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        if ($this->sms && config('noc.sms.enabled')) {
            $channels[] = SmsChannel::class;
        }
        if (\App\Models\PushSubscription::where('user_id', $notifiable->id)->exists()) {
            $channels[] = \App\Channels\WebPushChannel::class;
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->title)->greeting('Hello '.$notifiable->name.',')->line($this->body);
        if ($this->url) {
            $mail->action('Open '.config('noc.brand'), $this->url);
        }
        return $mail;
    }

    public function toSms(object $notifiable): string
    {
        return config('noc.brand').': '.$this->body.($this->url ? ' '.$this->url : '');
    }

    /** Phone/app notification: title, short text and the page to open when tapped. */
    public function toWebPush(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'url' => $this->url ?: url('/dashboard')];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'url' => $this->url];
    }
}
