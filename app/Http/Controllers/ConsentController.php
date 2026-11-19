<?php

namespace App\Http\Controllers;

use App\Models\Consent;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $missing = Consent::missingFor($user);
        if (! $missing) {
            return redirect()->route('dashboard');
        }
        return view('shared.consent', [
            'items' => collect($missing)->mapWithKeys(fn ($t) => [$t => Consent::statement($t, $user)]),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $missing = Consent::missingFor($user);
        $request->validate(collect($missing)->mapWithKeys(fn ($t) => ['consent.'.$t => 'accepted'])->all(),
            ['consent.*.accepted' => 'Please tick every statement to continue.']);

        foreach ($missing as $type) {
            Consent::record($user, $type, $request);
        }
        return redirect()->intended(route('dashboard'))->with('status', 'Thank you. Your agreement has been recorded.');
    }
}