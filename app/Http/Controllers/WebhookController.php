<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\KopoKopo;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function kopokopo(Request $request, KopoKopo $k2, SubscriptionService $subs)
    {
        $body = $request->getContent();
        if (! $k2->validSignature($body, $request->header('X-KopoKopo-Signature'))) {
            Log::warning('Kopo Kopo webhook: bad signature');
            return response()->json(['ok' => false], 401);
        }

        $json = json_decode($body, true) ?: [];
        $paymentId = $json['data']['attributes']['metadata']['payment_id'] ?? null;
        $payment = $paymentId ? Payment::find($paymentId) : null;

        // Never trust the callback body for money: re-read the result from Kopo Kopo itself.
        if ($payment && $payment->status === 'pending' && $payment->provider_location) {
            try {
                $k2->applyResult($payment, $k2->fetchStatus($payment) ?? [], $subs);
            } catch (\Throwable $e) {
                Log::error('Kopo Kopo status fetch failed: '.$e->getMessage());
            }
        }

        return response()->json(['ok' => true]);
    }
}
