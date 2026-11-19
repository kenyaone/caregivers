@extends('layouts.app', ['title' => 'Dashboard'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Hi {{ strtok(auth()->user()->name, ' ') }} 👋</h1>
        <div class="flex gap-2"><a href="{{ route('caregivers.index') }}" class="btn btn-ghost">Find caregivers</a><a href="{{ route('family.jobs.create') }}" class="btn btn-primary">+ Post a care job (free)</a></div>
    </div>
    @unless($profile->isComplete())
        <div class="alert alert-info mt-6">Add your county and town on <a class="link" href="{{ route('family.profile') }}">your profile</a> so caregivers know where you are.</div>
    @endunless
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="card"><div class="text-sm text-slate-500">Contact credits left</div><div class="text-3xl font-bold mt-1">{{ $credits }}</div><a href="{{ route('payments.index') }}" class="link text-xs">Buy a contact pass →</a></div>
        <div class="card"><div class="text-sm text-slate-500">Caregivers you've unlocked</div><div class="text-3xl font-bold mt-1">{{ $unlocked->count() }}</div><a href="{{ route('family.contacts') }}" class="link text-xs">View contacts →</a></div>
        <div class="card"><div class="text-sm text-slate-500">ID-verified badge</div><div class="mt-2"><x-status :value="$profile->verification_status === 'incomplete' ? 'not requested' : $profile->verification_status"/></div>
            @if(in_array($profile->verification_status, ['incomplete', 'rejected']))<form method="POST" action="{{ route('family.verify') }}" class="mt-2">@csrf<button class="link text-xs">Upload your ID, then request the badge →</button></form>@endif</div>
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card">
            <div class="flex items-center justify-between"><h2 class="font-semibold">Your care jobs</h2><a href="{{ route('family.jobs.index') }}" class="link text-sm">All →</a></div>
            <div class="mt-3 divide-y">
                @forelse($jobs as $j)
                    <div class="py-3 flex items-center justify-between gap-3"><div><a class="link" href="{{ route('family.jobs.show', $j) }}">{{ $j->title }}</a><div class="text-xs text-slate-500">{{ $j->careTypeLabel() }} · {{ $j->town }}</div></div><div class="text-right space-y-1"><x-status :value="$j->status"/>@if($j->new_count)<div class="text-xs"><span class="badge bg-amber-100 text-amber-800">{{ $j->new_count }} new</span></div>@endif</div></div>
                @empty
                    <p class="text-sm text-slate-500 py-2">No jobs yet. <a class="link" href="{{ route('family.jobs.create') }}">Post one for free</a>.</p>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="flex items-center justify-between"><h2 class="font-semibold">Requests you sent</h2><a href="{{ route('family.requests') }}" class="link text-sm">All →</a></div>
            <div class="mt-3 divide-y">
                @forelse($requests as $r)
                    <div class="py-3 flex items-center justify-between gap-3"><div><a class="link" href="{{ route('caregivers.show', $r->caregiver) }}">{{ $r->caregiver->publicName() }}</a><div class="text-xs text-slate-500">{{ config('noc.care_types')[$r->care_type] ?? '' }} · {{ $r->created_at->diffForHumans() }}</div></div><x-status :value="$r->status"/></div>
                @empty
                    <p class="text-sm text-slate-500 py-2">No requests yet. <a class="link" href="{{ route('caregivers.index') }}">Browse caregivers</a>.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
