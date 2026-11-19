@extends('layouts.app', ['title' => 'Review '.$subject->name])
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <a href="{{ route('admin.verifications') }}" class="link text-sm">← Verifications</a>
    <div class="card mt-4">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-4">
                @if($subject->isCaregiver() && $profile->photoUrl())<img src="{{ $profile->photoUrl() }}" alt="" class="h-20 w-20 rounded-xl object-cover">@endif
                <div><h1 class="text-2xl font-bold">{{ $subject->name }}</h1><div class="text-sm text-slate-500">{{ ucfirst($subject->role) }} · {{ $subject->email }} · +{{ $subject->phone }} · joined {{ $subject->created_at->format('j M Y') }}</div></div>
            </div>
            <x-status :value="$profile->verification_status"/>
        </div>
        <dl class="mt-6 grid sm:grid-cols-2 gap-3 text-sm">
            @foreach(collect($profile->getAttributes())->except(['id', 'user_id', 'created_at', 'updated_at', 'verification_status', 'verification_checks', 'verified_by', 'rating_avg', 'rating_count', 'photo_path', 'good_conduct_warned_at']) as $k => $v)
                <div><dt class="text-slate-500">{{ ucfirst(str_replace('_', ' ', $k)) }}</dt><dd class="font-semibold break-words">{{ $v === null || $v === '' ? '—' : (is_string($v) && str_starts_with($v, '[') ? implode(', ', json_decode($v, true) ?: []) : $v) }}</dd></div>
            @endforeach
        </dl>
        <div class="alert alert-info mt-4 text-sm space-y-1">
            @if($subject->isCaregiver())
                <div><b>How to check:</b> open the ID, the selfie holding the ID and the profile photo side by side: they must be the same person and the ID in the selfie must match the uploaded ID. Check the Certificate of Good Conduct is in their name, shows no record and was issued in the last {{ config('skills.good_conduct_valid_months') }} months, and verify its serial or QR code. Phone at least one referee and the caregiver themselves.</div>
            @else
                <div><b>How to check:</b> compare the ID with the account name and phone the family member.</div>
            @endif
            @if($profile->verified_by)<div class="text-xs">Last decision by {{ $profile->verification_checks['by'] ?? 'admin' }} on {{ $profile->verification_checks['at'] ?? $profile->updated_at }}.</div>@endif
        </div>
        <h2 class="font-semibold mt-6">Documents</h2>
        <ul class="mt-2 space-y-1 text-sm">@forelse($subject->documents as $d)<li>{{ $d->label() }}: <a class="link" target="_blank" href="{{ route('documents.download', $d) }}">{{ $d->original_name }}</a> <span class="text-xs text-slate-500">({{ $d->created_at->format('j M Y') }})</span></li>@empty<li class="text-slate-500">No documents uploaded.</li>@endforelse</ul>
        <form method="POST" action="{{ route('admin.verifications.decide', $subject) }}" class="mt-6 border-t pt-6 grid gap-3 md:grid-cols-3 items-end">
            @csrf
            <div class="md:col-span-3 space-y-2">
                <div class="label">Verification checks (all required to approve)</div>
                @foreach($checklist as $key => $text)<label class="flex items-start gap-2 text-sm"><input type="checkbox" name="checks[{{ $key }}]" value="1" class="mt-1 rounded" @checked(old('checks.'.$key))> <span>{{ $text }}</span></label>@endforeach
                @error('checks')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            @if($subject->isCaregiver())
                <div class="md:col-span-3"><label class="label" for="gc-date">Good Conduct certificate issue date (required to approve)</label>
                    <input id="gc-date" type="date" name="good_conduct_issued_on" class="input" style="max-width:14rem" value="{{ old('good_conduct_issued_on', $profile->good_conduct_issued_on?->toDateString()) }}" max="{{ now()->toDateString() }}"></div>
            @endif
            <x-select name="decision" label="Decision" :options="['approved' => 'Approve', 'rejected' => 'Reject / request changes']" :placeholder="false"/>
            <x-field name="note" label="Note (required if rejecting)" placeholder="e.g. Good Conduct certificate is older than 12 months"/>
            <button class="btn btn-primary">Save decision</button>
        </form>
        @if($subject->isCaregiver() && $profile->verification_status === 'approved')
            <div class="mt-8 border-t pt-6 grid gap-4 md:grid-cols-2">
                <form method="POST" action="{{ route('admin.verifications.goodconduct', $subject) }}" class="space-y-2">
                    @csrf
                    <div class="font-semibold">Good Conduct</div>
                    <div class="text-sm">Issued {{ $profile->good_conduct_issued_on?->format('j M Y') ?? 'not recorded' }} · state: <b>{{ $profile->goodConductState() }}</b>@if($profile->goodConductValidUntil()) · counts until {{ $profile->goodConductValidUntil()->format('j M Y') }}@endif</div>
                    <div class="flex flex-wrap items-end gap-2"><input type="date" name="good_conduct_issued_on" class="input" style="max-width:12rem" max="{{ now()->toDateString() }}" required><button class="btn btn-ghost btn-sm">Record new certificate date</button></div>
                </form>
                <form method="POST" action="{{ route('admin.caregivers.pause', $subject) }}" class="space-y-2">
                    @csrf
                    <div class="font-semibold">Listing</div>
                    <div class="text-sm">{{ $profile->paused_at ? 'Paused '.$profile->paused_at->format('j M Y').' ('.$profile->paused_reason.')' : 'Not paused' }}</div>
                    <button class="btn btn-ghost btn-sm">{{ $profile->paused_at ? 'List again' : 'Pause listing' }}</button>
                </form>
            </div>
        @endif
        <h2 class="font-semibold mt-8">Agreements on record</h2>
        <div class="mt-2 text-xs text-slate-600 space-y-1">
            @forelse($consents as $c)<div><b>{{ str_replace('_', ' ', $c->type) }}</b> (v{{ $c->version }}) · {{ $c->created_at->format('j M Y H:i') }} · IP {{ $c->ip ?: 'n/a' }}@if($c->context_type) · {{ $c->context_type }} #{{ $c->context_id }}@endif</div>@empty<div>None yet.</div>@endforelse
        </div>
    </div>
</div>
@endsection
