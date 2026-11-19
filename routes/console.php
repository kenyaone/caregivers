<?php

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')->everyMinute()->withoutOverlapping();

// Remind caregivers 7 days before their subscription ends (their profile stops being listed).
Schedule::call(function () {
    Subscription::with('user')->whereBetween('ends_at', [now()->addDays(7)->startOfDay(), now()->addDays(7)->endOfDay()])->get()
        ->each(function ($sub) {
            if ($sub->user->activeSubscription()?->id === $sub->id) {
                $sub->user->notify(new PlatformNotice('Subscription ending soon', 'Your subscription ends on '.$sub->ends_at->format('j M Y').'. Renew to stay visible to families.', route('payments.index')));
            }
        });
})->dailyAt('09:00')->name('subscription-reminders');

// Close care jobs nobody has updated for 60 days.
Schedule::call(fn () => \App\Models\CareJob::where('status', 'open')->where('updated_at', '<', now()->subDays(60))->update(['status' => 'closed']))
    ->dailyAt('01:00')->name('close-stale-jobs');

Artisan::command('noc:admin {email} {password} {name=Administrator}', function ($email, $password, $name) {
    $user = User::updateOrCreate(['email' => strtolower($email)], ['name' => $name, 'password' => $password, 'role' => 'admin', 'is_active' => true]);
    $this->info('Admin ready: '.$user->email);
})->purpose('Create or update an admin account');
