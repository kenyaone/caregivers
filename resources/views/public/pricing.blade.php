@extends('layouts.app', ['title' => 'Pricing'])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="text-center">
        <h1 class="text-3xl font-bold">Simple pricing</h1>
        <p class="mt-2 text-slate-600 max-w-2xl mx-auto">Free for families. Caregivers subscribe to be listed: from 3 months (KES 300) or a full year (KES 1,200). We never take a cut of a caregiver's pay.</p>
    </div>
    <h2 class="text-xl font-bold mt-10">For families</h2>
    <div class="card mt-4 md:flex md:items-center md:justify-between gap-6" style="border-color:#99f6e4;background:#f0fdfa">
        <div>
            <div class="text-4xl font-extrabold text-teal-800">Free</div>
            <ul class="mt-3 space-y-1 text-sm text-slate-700">
                <li>✓ Browse verified caregivers, their checks, skills and reviews</li>
                <li>✓ See phone numbers and call caregivers directly</li>
                <li>✓ Post care jobs and receive applications</li>
            </ul>
        </div>
        <a href="{{ auth()->check() ? route('caregivers.index') : route('register') }}" class="btn btn-primary mt-6 md:mt-0">{{ auth()->check() ? 'Find a caregiver' : 'Create a free family account' }}</a>
    </div>
    <h2 class="text-xl font-bold mt-10">For caregivers</h2>
    <div class="mt-4 grid gap-6 md:grid-cols-3">
        @foreach($caregiverPlans as $plan)
            <div class="card text-center">
                <h3 class="font-bold text-lg">{{ $plan->name }}</h3>
                <div class="mt-3 text-4xl font-extrabold text-teal-800">KES {{ number_format($plan->price) }}</div>
                <div class="text-sm text-slate-500">{{ $plan->duration_days }} days</div>
                <p class="mt-4 text-sm text-slate-600">{{ $plan->description }}</p>
                <a href="{{ auth()->user()?->isCaregiver() ? route('payments.index') : route('register', ['as' => 'caregiver']) }}" class="btn btn-primary w-full mt-6">Join</a>
            </div>
        @endforeach
    </div>
</div>
@endsection
