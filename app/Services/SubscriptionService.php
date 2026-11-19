<?php

namespace App\Services;

use App\Models\ContactPass;
use App\Models\Payment;
use App\Models\Subscription;
use App\Notifications\PlatformNotice;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Marks a payment paid and grants what was bought: a caregiver subscription
     * (extends any active one) or a family contact pass. Idempotent.
     */
    public function markPaid(Payment $payment)
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::lockForUpdate()->find($payment->id);
            if ($payment->status === 'paid') {
                return null;
            }
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            $user = $payment->user;
            $plan = $payment->plan;

            if ($plan->audience === 'family') {
                $pass = ContactPass::create([
                    'user_id' => $user->id, 'plan_id' => $plan->id, 'payment_id' => $payment->id,
                    'credits_total' => $plan->credits ?: 1, 'expires_at' => now()->addDays($plan->duration_days),
                ]);
                $user->notify(new PlatformNotice('Contact pass active',
                    'Payment of KES '.number_format($payment->amount).' received. You can unlock '.$pass->credits_total.' caregiver contact(s) until '.$pass->expires_at->format('j M Y').'.',
                    route('caregivers.index')));
                return $pass;
            }

            $current = $user->activeSubscription();
            $start = $current ? $current->ends_at : now();
            $sub = Subscription::create([
                'user_id' => $user->id, 'plan_id' => $plan->id, 'payment_id' => $payment->id,
                'starts_at' => $start, 'ends_at' => $start->copy()->addDays($plan->duration_days),
            ]);
            $user->notify(new PlatformNotice('Subscription active',
                'Payment of KES '.number_format($payment->amount).' received. Your profile is listed and you can apply for jobs until '.$sub->ends_at->format('j M Y').'.',
                route('caregiver.dashboard')));
            return $sub;
        });
    }
}
