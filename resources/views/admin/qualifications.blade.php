@extends('layouts.app', ['title' => 'College certificates'])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">College certificates</h1>
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach(['pending' => 'To check', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $l)<a href="{{ route('admin.qualifications', ['status' => $k]) }}" class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $l }}</a>@endforeach
            <a href="{{ route('admin.skills') }}" class="btn btn-sm btn-ghost">Skill certificates →</a>
        </div>
    </div>
    <p class="text-sm text-slate-600 mt-2">Check the certificate is in the caregiver's name and matches the course, college and year they entered. If unsure, phone the college's exams office to confirm the certificate number.</p>
    <div class="mt-6 space-y-3">
        @forelse($items as $q)
            <div class="card flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-semibold">{{ $q->levelLabel() }} in {{ $q->course }} · <a class="link" href="{{ route('admin.verifications.show', $q->user) }}">{{ $q->user->name }}</a></div>
                    <div class="text-sm text-slate-500">{{ $q->institution }}@if($q->year) · {{ $q->year }}@endif
                        @if($q->document) · <a class="link" target="_blank" href="{{ route('documents.download', $q->document) }}">{{ $q->document->original_name }}</a>@endif
                        @if($q->note) · {{ $q->note }}@endif</div>
                </div>
                @if($q->status === 'pending')
                    <form method="POST" action="{{ route('admin.qualifications.decide', $q) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <input name="note" class="input" style="width:14rem" maxlength="255" placeholder="Reason if rejecting">
                        <button name="decision" value="approved" class="btn btn-primary btn-sm">Approve</button>
                        <button name="decision" value="rejected" class="btn btn-ghost btn-sm">Reject</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="card text-slate-500">Nothing here.</div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $items])
</div>
@endsection
