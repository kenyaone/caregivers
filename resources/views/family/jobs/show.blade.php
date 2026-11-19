@extends('layouts.app', ['title' => $job->title])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <a href="{{ route('family.jobs.index') }}" class="link text-sm">← My care jobs</a>
    <div class="card mt-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="text-xs font-semibold uppercase text-teal-700">{{ $job->careTypeLabel() }} · {{ $job->arrangementLabel() }}</div>
            <h1 class="text-2xl font-bold mt-1">{{ $job->title }}</h1>
            <div class="text-sm mt-1">{{ $job->town }}, {{ $job->county }} · <b>{{ $job->payLabel() }}</b></div>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <x-status :value="$job->status"/>
            @if($job->status === 'open')
                <a href="{{ route('family.jobs.edit', $job) }}" class="btn btn-ghost btn-sm">Edit</a>
                <form method="POST" action="{{ route('family.jobs.close', $job) }}">@csrf<button class="btn btn-ghost btn-sm">Close job</button></form>
            @endif
        </div>
    </div>
    <div class="mt-6 flex items-center justify-between"><h2 class="font-semibold text-lg">Applicants</h2><span class="text-sm text-slate-500">Contact credits left: <b>{{ $credits }}</b> · <a class="link" href="{{ route('payments.index') }}">Buy more</a></span></div>
    <div class="mt-3 space-y-4">
        @forelse($job->applications as $a)
            @php($c = $a->caregiver)
            @php($p = $c->caregiverProfile)
            <div class="card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if($p->photoUrl())<img src="{{ $p->photoUrl() }}" alt="" class="h-14 w-14 rounded-xl object-cover">@endif
                        <div><a href="{{ route('caregivers.show', $c) }}" class="font-bold link">{{ auth()->user()->hasUnlocked($c) ? $c->name : $c->publicName() }}</a><div class="text-sm text-slate-600">{{ $p->headline }} · {{ $p->years_experience ?? 0 }} yrs · <x-stars :avg="$p->rating_avg" :count="$p->rating_count"/></div></div>
                    </div>
                    <x-status :value="$a->status"/>
                </div>
                <p class="text-sm mt-3 whitespace-pre-line">{{ $a->message }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if(auth()->user()->hasUnlocked($c))
                        <a class="btn btn-ghost btn-sm" href="tel:+{{ $c->phone }}">📞 +{{ $c->phone }}</a>
                    @else
                        <form method="POST" action="{{ route('family.unlock', $c) }}">@csrf<button class="btn btn-ghost btn-sm" onclick="return confirm('Use 1 credit to unlock this contact?')">Unlock contact (1 credit)</button></form>
                    @endif
                    @if(in_array($a->status, ['applied', 'shortlisted']))
                        <form method="POST" action="{{ route('family.applications.decide', $a) }}" class="flex gap-2">@csrf
                            @if($a->status === 'applied')<button name="status" value="shortlisted" class="btn btn-ghost btn-sm">Shortlist</button>@endif
                            <button name="status" value="hired" class="btn btn-primary btn-sm" onclick="return confirm('Mark as hired? The job will be closed.')">Hire</button>
                            <button name="status" value="rejected" class="btn btn-ghost btn-sm">Decline</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card text-sm text-slate-500">No applicants yet. Matching verified caregivers in {{ $job->county }} were alerted. You can also <a class="link" href="{{ route('caregivers.index', ['care_type' => $job->care_type, 'county' => $job->county]) }}">browse caregivers</a> and send them a request.</div>
        @endforelse
    </div>
</div>
@endsection
