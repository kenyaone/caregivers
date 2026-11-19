<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Http\Request;

/** Manual M-Pesa payments (Buy Goods), confirmed by an admin. */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $audience = $user->isCaregiver() ? 'caregiver' : 'family';
        return view('shared.payments', [
            'plans' => Plan::for($audience)->get(),
            'payments' => $user->payments()->with('plan')->latest()->limit(10)->get(),
            'subscription' => $user->isCaregiver() ? $user->activeSubscription() : null,
            'passes' => $user->isFamily() ? $user->contactPasses()->where('expires_at', '>', now())->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'reference' => ['required', 'regex:/^[A-Za-z0-9]{10}$/', 'unique:payments,reference'],
            'phone' => ['required', 'regex:/^(\+?254|0)?[17]\d{8}$/'],
        ], ['reference.regex' => 'An M-Pesa code has 10 letters/numbers, e.g. SKJ3XYZ12A.', 'reference.unique' => 'That M-Pesa code has already been submitted.']);

        $plan = Plan::for($user->isCaregiver() ? 'caregiver' : 'family')->findOrFail($data['plan_id']);
        Payment::create([
            'user_id' => $user->id, 'plan_id' => $plan->id, 'amount' => $plan->price,
            'phone' => User::normalisePhone($data['phone']), 'method' => 'manual',
            'reference' => strtoupper($data['reference']), 'status' => 'pending',
        ]);
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice(
            'Payment to confirm', $user->name.' submitted M-Pesa code '.strtoupper($data['reference']).' for '.$plan->name.' (KES '.number_format($plan->price).').', route('admin.payments')
        ));
        return back()->with('status', 'Payment submitted. We will activate it once we confirm the M-Pesa code.');
    }
}
