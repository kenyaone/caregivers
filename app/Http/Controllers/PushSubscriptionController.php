<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

/** Saves a phone/browser's push subscription so shift alerts can reach it. */
class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint'    => 'required|url|max:2000',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth'   => 'required|string|max:255',
        ]);
        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id'    => $request->user()->id,
                'endpoint'   => $data['endpoint'],
                'p256dh'     => $data['keys']['p256dh'],
                'auth'       => $data['keys']['auth'],
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $endpoint = (string) $request->input('endpoint', '');
        PushSubscription::where('user_id', $request->user()->id)->where('endpoint_hash', hash('sha256', $endpoint))->delete();

        return response()->json(['ok' => true]);
    }
}
