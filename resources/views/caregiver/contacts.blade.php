@extends('layouts.app', ['title' => 'Families who contacted me'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Families who viewed your number</h1>
    <p class="text-sm text-slate-600 mt-1">These families may call you. Only meet in safe places, and never pay anyone to "secure" a job.</p>
    <div class="card mt-6 divide-y">
        @forelse($unlocks as $u)
            <div class="py-3 flex flex-wrap justify-between gap-3 text-sm">
                <div><b>{{ $u->family->name }}</b> @if($u->family->familyProfile?->verification_status === 'approved')<span class="badge bg-emerald-100 text-emerald-800">✓ ID verified</span>@endif<div class="text-slate-500">{{ $u->family->familyProfile?->town }}{{ $u->family->familyProfile?->county ? ', '.$u->family->familyProfile->county : '' }}</div></div>
                <div class="text-right"><a class="link" href="tel:+{{ $u->family->phone }}">+{{ $u->family->phone }}</a><div class="text-xs text-slate-500">{{ $u->created_at->diffForHumans() }}</div></div>
            </div>
        @empty
            <p class="text-sm text-slate-500 py-2">No families yet.</p>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $unlocks])
</div>
@endsection
