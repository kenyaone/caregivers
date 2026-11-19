@extends('layouts.app', ['title' => auth()->user()->isCaregiver() ? 'Subscription' : 'Contact passes'])
@section('content')
@php($isCg = auth()->user()->isCaregiver())
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">{{ $isCg ? 'Caregiver subscription' : 'Contact passes' }}</h1>
    <div class="card mt-6 text-sm">
        @if($isCg)
            {!! $subscription ? '✅ You are listed until <b>'.e($subscription->ends_at->format('l, j F Y')).'</b>. Paying again extends it.' : 'You are not listed yet. Once verified, subscribe so families can find you and you can apply for jobs.' !!}
        @else
            @forelse($passes as $p)
                <div>✅ {{ $p->plan->name }}: <b>{{ $p->credits_total - $p->credits_used }}</b> of {{ $p->credits_total }} contacts left, until {{ $p->expires_at->format('j M Y') }}</div>
            @empty
                You have no active contact pass. Buy one to unlock caregivers' phone numbers.
            @endforelse
        @endif
    </div>
    <form method="POST" action="{{ route('payments.store') }}" class="mt-6 space-y-6">
        @csrf
        <div class="grid gap-4 md:grid-cols-3">
            @foreach($plans as $plan)
                <label class="card cursor-pointer has-[:checked]:border-teal-600 has-[:checked]:ring-2 has-[:checked]:ring-teal-600">
                    <input type="radio" name="plan_id" value="{{ $plan->id }}" class="sr-only" @checked(old('plan_id', $plans->first()->id) == $plan->id)>
                    <div class="font-bold">{{ $plan->name }}</div>
                    <div class="text-2xl font-extrabold text-teal-800 mt-1">KES {{ number_format($plan->price) }}</div>
                    <div class="text-xs text-slate-500">@if($plan->credits){{ $plan->credits }} contacts · @endif{{ $plan->duration_days }} days</div>
                    <p class="text-xs text-slate-600 mt-2">{{ $plan->description }}</p>
                </label>
            @endforeach
        </div>
        <div class="card space-y-4">
            <h2 class="font-semibold">Pay with M-Pesa</h2>
            @if(config('noc.mpesa_till'))
                <ol class="text-sm text-slate-700 list-decimal pl-5 space-y-1">
                    <li>Go to M-Pesa → <b>Lipa na M-Pesa</b> → <b>Buy Goods and Services</b></li>
                    <li>Till number: <b class="text-lg">{{ config('noc.mpesa_till') }}</b></li>
                    <li>Pay the amount for your chosen option, then enter the M-Pesa code below</li>
                </ol>
            @endif
            <div class="grid gap-4 md:grid-cols-2">
                <x-field name="reference" label="M-Pesa transaction code" placeholder="e.g. SKJ3XYZ12A" maxlength="10" class="uppercase" required/>
                <x-field name="phone" label="Number you paid from" :value="auth()->user()->phone ? '0'.substr(auth()->user()->phone, 3) : ''" required/>
            </div>
            <button class="btn btn-primary">Submit payment</button>
            <p class="text-xs text-slate-500">We confirm payments quickly during working hours. You'll get an alert as soon as it's active.</p>
        </div>
    </form>
    @if($payments->count())
        <div class="card mt-6 overflow-x-auto">
            <h2 class="font-semibold">Payment history</h2>
            <table class="table mt-3">
                <thead><tr><th>Date</th><th>Option</th><th>Amount</th><th>Code</th><th>Status</th></tr></thead>
                <tbody>@foreach($payments as $p)<tr><td>{{ $p->created_at->format('j M Y H:i') }}</td><td>{{ $p->plan->name }}</td><td>KES {{ number_format($p->amount) }}</td><td>{{ $p->reference }}</td><td><x-status :value="$p->status"/></td></tr>@endforeach</tbody>
            </table>
        </div>
    @endif
</div>
@endsection
