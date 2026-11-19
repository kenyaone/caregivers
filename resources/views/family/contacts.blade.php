@extends('layouts.app', ['title' => 'My caregivers'])
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Caregivers you've unlocked</h1>
    <div class="alert alert-info mt-4 text-sm">Before agreeing anything: meet in person, see the original ID and Certificate of Good Conduct, and call at least one reference. Then agree pay, duties and days off in writing.</div>
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @forelse($unlocks as $u)
            @php($p = $u->caregiver->caregiverProfile)
            <div class="card">
                <div class="flex items-center gap-3">
                    @if($p->photoUrl())<img src="{{ $p->photoUrl() }}" alt="" class="h-14 w-14 rounded-xl object-cover">@endif
                    <div><a class="font-bold link" href="{{ route('caregivers.show', $u->caregiver) }}">{{ $u->caregiver->name }}</a><div class="text-xs text-slate-500">{{ $p->headline }}</div></div>
                </div>
                <div class="mt-3 text-sm">📞 <a class="link" href="tel:+{{ $u->caregiver->phone }}">+{{ $u->caregiver->phone }}</a> · {{ $u->caregiver->email }}</div>
                <div class="text-xs text-slate-500 mt-1">Unlocked {{ $u->created_at->format('j M Y') }} · <a class="link" href="{{ route('caregivers.show', $u->caregiver) }}">Leave a review</a></div>
            </div>
        @empty
            <div class="card text-center text-slate-500" style="grid-column:1/-1">You haven't unlocked any caregivers yet. <a class="link" href="{{ route('caregivers.index') }}">Browse caregivers</a></div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $unlocks])
</div>
@endsection
