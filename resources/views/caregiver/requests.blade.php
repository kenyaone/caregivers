@extends('layouts.app', ['title' => 'Care requests'])
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Care requests from families</h1>
    <div class="mt-6 space-y-4">
        @forelse($requests as $r)
            <div class="card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="font-bold">{{ $r->family->name }} @if($r->family->familyProfile?->verification_status === 'approved')<span class="badge bg-emerald-100 text-emerald-800">✓ ID verified</span>@endif</div>
                        <div class="text-sm text-slate-500">{{ $r->family->familyProfile?->town }}{{ $r->family->familyProfile?->county ? ', '.$r->family->familyProfile->county : '' }} · {{ config('noc.care_types')[$r->care_type] ?? '' }} · {{ config('noc.arrangements')[$r->arrangement] ?? '' }}@if($r->start_date) · from {{ $r->start_date->format('j M') }}@endif</div>
                    </div>
                    <x-status :value="$r->status"/>
                </div>
                <p class="mt-3 text-sm whitespace-pre-line">{{ $r->message }}</p>
                @if($r->status === 'pending')
                    <form method="POST" action="{{ route('caregiver.requests.respond', $r) }}" class="mt-4 flex gap-2">@csrf
                        <button name="decision" value="accepted" class="btn btn-primary btn-sm">I'm interested</button>
                        <button name="decision" value="declined" class="btn btn-ghost btn-sm">Not available</button>
                    </form>
                    <p class="text-xs text-slate-500 mt-2">If you're interested, the family is told and can call you.</p>
                @endif
            </div>
        @empty
            <div class="card text-center text-slate-500">No requests yet. A complete profile with a good photo gets more requests.</div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $requests])
</div>
@endsection
