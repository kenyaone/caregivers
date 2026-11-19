@extends('layouts.app', ['title' => 'Reports'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Reports</h1>
        <div class="flex gap-2 text-sm">@foreach(['open' => 'Open', 'upheld' => 'Upheld', 'dismissed' => 'Dismissed'] as $k => $l)<a href="{{ route('admin.reports', ['status' => $k]) }}" class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $l }}</a>@endforeach</div>
    </div>
    <p class="text-sm text-slate-600 mt-2">A caregiver is hidden automatically when {{ config('skills.auto_pause_reporters') }} different people report them, or when a family who unlocked or used them reports unsafe behaviour. Call both sides before deciding.</p>
    <div class="mt-6 space-y-4">
        @forelse($reports as $r)
            <div class="card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold">{{ $r->reasonLabel() }}</div>
                        <div class="text-sm text-slate-500">
                            <b>{{ $r->reporter->name }}</b> ({{ $r->reporter->role }}, +{{ $r->reporter->phone }}) reported
                            <a class="link" href="{{ route('admin.verifications.show', $r->reported) }}">{{ $r->reported->name }}</a> ({{ $r->reported->role }}, +{{ $r->reported->phone }})
                            · {{ $r->created_at->format('j M Y H:i') }}@if($r->job) · job: {{ $r->job->title }}@endif
                        </div>
                    </div>
                    @if($r->reported->isCaregiver())
                        <form method="POST" action="{{ route('admin.caregivers.pause', $r->reported) }}">@csrf
                            <button class="btn btn-sm {{ $r->reported->caregiverProfile->paused_at ? 'btn-primary' : 'btn-ghost' }}">{{ $r->reported->caregiverProfile->paused_at ? 'Paused: list again' : 'Pause listing' }}</button></form>
                    @endif
                </div>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $r->details }}</p>
                @if($r->status === 'open')
                    <form method="POST" action="{{ route('admin.reports.decide', $r) }}" class="mt-4 flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="flex-1 min-w-0"><label class="label">Note</label><input name="admin_note" class="input" maxlength="255" placeholder="What you found / action taken"></div>
                        <button name="status" value="upheld" class="btn btn-primary btn-sm">Uphold</button>
                        <button name="status" value="dismissed" class="btn btn-ghost btn-sm">Dismiss</button>
                    </form>
                @else
                    <p class="mt-3 text-xs text-slate-500">{{ ucfirst($r->status) }} {{ $r->resolved_at?->format('j M Y') }}@if($r->admin_note): {{ $r->admin_note }}@endif</p>
                @endif
            </div>
        @empty
            <div class="card text-slate-500">No {{ $status }} reports.</div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $reports])
</div>
@endsection
