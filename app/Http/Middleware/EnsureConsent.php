<?php

namespace App\Http\Middleware;

use App\Models\Consent;
use Closure;
use Illuminate\Http\Request;

/** Sends nurses and facilities to the consent page until they accept the current terms. */
class EnsureConsent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && Consent::missingFor($user)) {
            if ($request->isMethod('GET')) {
                session()->put('url.intended', $request->fullUrl());
            }
            return redirect()->route('consent.show');
        }
        return $next($request);
    }
}