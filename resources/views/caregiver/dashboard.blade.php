@extends('layouts.app', ['title' => 'Dashboard'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Hi {{ strtok(auth()->user()->name, ' ') }} 👋</h1>
    @php($steps = [
        ['Complete profile & photo', $profile->isComplete() && $profile->photo_path, route('caregiver.profile')],
        ['Upload ID & Good Conduct', $docTypes->contains('national_id') && $docTypes->contains('good_conduct'), route('documents.index')],
        ['Get verified', $profile->verification_status === 'approved', null],
        ['Subscribe to be listed', (bool) $subscription, route('payments.index')],
    ])
    @if(collect($steps)->contains(fn ($s) => ! $s[1]))
    <div class="card mt-6">
        <h2 class="font-semibold">Get listed for families</h2>
        <ol class="mt-4 grid gap-3 md:grid-cols-4">
            @foreach($steps as $i => [$label, $done, $url])
                <li class="rounded-xl border p-3 {{ $done ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200' }}">
                    <div class="text-xs text-slate-500">Step {{ $i + 1 }}</div>
                    <div class="font-semibold text-sm mt-1">{{ $done ? '✅' : '⬜' }} {{ $label }}</div>
                    @if(! $done && $url)<a href="{{ $url }}" class="link text-xs">Do this now →</a>@endif
                </li>
            @endforeach
        </ol>
        <div class="mt-4 text-sm">
            @if($profile->verification_status === 'pending')<div class="alert alert-info">We're checking your documents and will call your referee. We'll notify you.</div>@endif
            @if($profile->verification_status === 'rejected')<div class="alert alert-error">Verification needs attention: {{ $profile->verification_note }}</div>@endif
            @if(in_array($profile->verification_status, ['incomplete', 'rejected']))
                <p class="text-slate-600">Tip: also upload a reference letter or put a referee's name and phone in "About me". We call one referee before approving.</p>
                <form method="POST" action="{{ route('caregiver.verify') }}" class="mt-3">@csrf<button class="btn btn-primary">Submit for verification</button></form>
            @endif
        </div>
    </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <div class="flex items-center justify-between"><h2 class="font-semibold">Care requests waiting for you</h2><a href="{{ route('caregiver.requests') }}" class="link text-sm">All requests →</a></div>
                <div class="mt-3 divide-y">
                    @forelse($pendingRequests as $r)
                        <div class="py-3 flex flex-wrap items-center justify-between gap-3">
                            <div><div class="font-semibold">{{ $r->family->name }} · {{ config('noc.care_types')[$r->care_type] ?? '' }}</div><div class="text-sm text-slate-500">{{ $r->family->familyProfile?->town }} · {{ config('noc.arrangements')[$r->arrangement] ?? '' }}</div></div>
                            <a href="{{ route('caregiver.requests') }}" class="btn btn-primary btn-sm">Respond</a>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 py-2">No new requests.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between"><h2 class="font-semibold">Care jobs that match you</h2><a href="{{ route('jobs.index', ['county' => $profile->county]) }}" class="link text-sm">More →</a></div>
                <div class="mt-3 grid gap-4 md:grid-cols-2">@forelse($matchingJobs as $job)<x-job-card :job="$job"/>@empty<p class="text-sm text-slate-500">None right now. We'll alert you when one is posted.</p>@endforelse</div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="card">
                <h2 class="font-semibold">Subscription</h2>
                <p class="mt-2 text-sm">{!! $subscription ? 'Listed until <b>'.e($subscription->ends_at->format('j M Y')).'</b>' : 'Not listed. Subscribe once verified.' !!}</p>
                <a href="{{ route('payments.index') }}" class="btn btn-ghost w-full mt-4">{{ $subscription ? 'Renew' : 'Subscribe' }}</a>
            </div>
            <div class="card text-sm space-y-1">
                <h2 class="font-semibold text-base">Your profile</h2>
                <div>Status: <x-status :value="$profile->verification_status"/></div>
                <div>Rating: <x-stars :avg="$profile->rating_avg" :count="$profile->rating_count"/></div>
                <div>Families who unlocked you: <b>{{ $unlocks }}</b> · <a href="{{ route('caregiver.contacts') }}" class="link">See who</a></div>
                <div>Available: <b>{{ $profile->available ? 'Yes' : 'No (hidden from search)' }}</b></div>
                <div class="flex gap-2 pt-3"><a href="{{ route('caregiver.profile') }}" class="btn btn-ghost btn-sm">Edit profile</a>@if($profile->verification_status === 'approved')<a href="{{ route('caregivers.show', auth()->user()) }}" class="btn btn-ghost btn-sm">View public profile</a>@endif</div>
            </div>
        </div>
    </div>
</div>
@endsection
