<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Kopo Kopo API v1: M-Pesa STK push for subscription payments. */
class KopoKopo
{
    public function enabled(): bool
    {
        return (bool) config('noc.kopokopo.enabled');
    }

    protected function token(): string
    {
        return Cache::remember('k2_token', 3000, function () {
            $cfg = config('noc.kopokopo');
            return Http::asForm()->acceptJson()->timeout(20)->post($cfg['base_url'].'/oauth/token', [
                'client_id' => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'grant_type' => 'client_credentials',
            ])->throw()->json('access_token');
        });
    }

    /** Sends the STK prompt; stores the request URL on the payment for status checks. */
    public function stkPush(Payment $payment): void
    {
        $user = $payment->user;
        [$first, $last] = array_pad(explode(' ', trim($user->name), 2), 2, '-');

        $response = Http::withToken($this->token())->acceptJson()->timeout(30)
            ->post(config('noc.kopokopo.base_url').'/api/v1/incoming_payments', [
                'payment_channel' => 'M-PESA STK Push',
                'till_number' => config('noc.kopokopo.till_number'),
                'subscriber' => [
                    'first_name' => $first,
                    'last_name' => $last,
                    'phone_number' => '+'.$payment->phone,
                    'email' => $user->email,
                ],
                'amount' => ['currency' => 'KES', 'value' => (string) $payment->amount],
                'metadata' => ['payment_id' => (string) $payment->id, 'notes' => 'Subscription: '.$payment->plan->name],
                '_links' => ['callback_url' => route('webhooks.kopokopo')],
            ])->throw();

        $payment->update(['provider_location' => $response->header('Location')]);
    }

    public function fetchStatus(Payment $payment): ?array
    {
        if (! $payment->provider_location) {
            return null;
        }
        return Http::withToken($this->token())->acceptJson()->timeout(20)->get($payment->provider_location)->throw()->json();
    }

    public function validSignature(string $body, ?string $signature): bool
    {
        $key = config('noc.kopokopo.api_key');
        if (! $key) {
            return true; // signature checking disabled until the API key is configured
        }
        return $signature && hash_equals(hash_hmac('sha256', $body, $key), $signature);
    }

    /**
     * Applies a Kopo Kopo incoming-payment result (from callback or polling).
     * Returns the resulting payment status.
     */
    public function applyResult(Payment $payment, array $json, SubscriptionService $subs): string
    {
        $attr = $json['data']['attributes'] ?? [];
        $status = $attr['status'] ?? null;
        $resource = $attr['event']['resource'] ?? [];

        if ($status === 'Success' || ($resource['status'] ?? null) === 'Received') {
            if ((int) round((float) ($resource['amount'] ?? $payment->amount)) < $payment->amount) {
                $payment->update(['status' => 'failed', 'raw' => $json]);
                return 'failed';
            }
            $payment->update(['reference' => $resource['reference'] ?? $payment->reference, 'raw' => $json]);
            $subs->markPaid($payment);
            return 'paid';
        }
        if ($status === 'Failed') {
            $payment->update(['status' => 'failed', 'raw' => $json]);
            return 'failed';
        }
        return $payment->status;
    }
}
