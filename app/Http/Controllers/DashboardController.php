<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return redirect()->route(match ($request->user()->role) {
            'admin' => 'admin.dashboard',
            'caregiver' => 'caregiver.dashboard',
            default => 'family.dashboard',
        });
    }
}
