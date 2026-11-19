@extends('layouts.app', ['title' => $job->title])
@section('content')
@php($u = auth()->user())
<div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('jobs.index') }}" class="link text-sm">← All care jobs</a>
    <div class="card mt-4">
        <div class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $job->careTypeLabel() }} · {{ $job->arrangementLabel() }}</div>
        <h1 class="text-2xl font-bold mt-1">{{ $job->title }}</h1>
        <div class="text-slate-600 mt-1">Posted by {{ $job->family->publicName() }} @if($job->family->familyProfile?->verification_status === 'approved')<span class="badge bg-emerald-100 text-emerald-800">✓ ID verified</span>@endif · {{ $job->created_at->diffForHumans() }}</div>
        <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-slate-500">Who needs care</dt><dd class="font-semibold">{{ $job->care_recipient }}</dd></div>
            <div><dt class="text-slate-500">Location</dt><dd class="font-semibold">{{ $job->town }}, {{ $job->county }}</dd></div>
            <div><dt class="text-slate-500">Pay offered</dt><dd class="font-semibold text-lg">{{ $job->payLabel() }}</dd></div>
            <div><dt class="text-slate-500">Start</dt><dd class="font-semibold">{{ $job->start_date?->format('j M Y') ?? 'Flexible' }}</dd></div>
        </dl>
        <div class="mt-6"><h2 class="font-semibold">Duties</h2><p class="mt-1 whitespace-pre-line text-slate-700">{{ $job->description }}</p></div>
        @if($job->requirements)<div class="mt-4"><h2 class="font-semibold">Requirements</h2><p class="mt-1 whitespace-pre-line text-slate-700">{{ $job->requirements }}</p></div>@endif

        <div class="mt-8 border-t pt-6">
            @guest
                <a href="{{ route('register', ['as' => 'caregiver']) }}" class="btn btn-primary">Join as a caregiver to apply</a>
            @else
                @if($u->isCaregiver())
                    @if($application)
                        <p class="text-sm">You applied: <x-status :value="$application->status"/> · <a class="link" href="{{ route('caregiver.applications') }}">My applications</a></p>
                    @elseif($job->status !== 'open')
                        <p class="text-slate-500">This job is no longer open.</p>
                    @elseif(! $u->isVerified())
                        <p class="text-sm text-slate-600">Get verified to apply. <a class="link" href="{{ route('caregiver.dashboard') }}">See what's missing</a></p>
                    @elseif(! $u->hasActiveSubscription())
                        <p class="text-sm text-slate-600">You need an active subscription to apply. <a class="link" href="{{ route('payments.index') }}">Subscribe</a></p>
                    @else
                        <form method="POST" action="{{ route('caregiver.apply', $job) }}" class="space-y-3">
                            @csrf
                            <x-textarea name="message" label="Why are you a good fit?" rows="3" placeholder="Your experience with similar care, when you can start, where you live…" required/>
                            <button class="btn btn-primary">Apply for this job</button>
                        </form>
                    @endif
                @elseif($u->id === $job->family_id)
                    <a href="{{ route('family.jobs.show', $job) }}" class="btn btn-primary">Manage this job</a>
                @endif
            @endguest
        </div>
    </div>
</div>
@endsection
