<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403);
        }
        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }
        return $next($request);
    }
}
