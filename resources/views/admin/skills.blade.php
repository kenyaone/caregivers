@extends('layouts.app', ['title' => 'Skill certificates'])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Skill certificates</h1>
        <div class="flex gap-2 text-sm">@foreach(['pending' => 'To check', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $l)<a href="{{ route('admin.skills', ['status' => $k]) }}" class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $l }}</a>@endforeach</div>
    </div>
    <p class="text-sm text-slate-600 mt-2">Check the certificate is in the caregiver's name, from a real training provider (e.g. St John Ambulance, Kenya Red Cross, a TVET college) and relevant to the skill. Phone the provider if unsure.</p>
    <div class="mt-6 space-y-3">
        @forelse($items as $s)
            <div class="card flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="font-semibold">{{ $s->label() }} · <a class="link" href="{{ route('admin.verifications.show', $s->user) }}">{{ $s->user->name }}</a></div>
                    <div class="text-sm">@if($s->certificate)<a class="link" target="_blank" href="{{ route('documents.download', $s->certificate) }}">{{ $s->certificate->original_name }}</a> · {{ $s->certificate->created_at->format('j M Y') }}@else <span class="text-slate-500">File removed</span>@endif
                        @if($s->cert_note) · {{ $s->cert_note }}@endif</div>
                </div>
                @if($s->cert_status === 'pending')
                    <form method="POST" action="{{ route('admin.skills.decide', $s) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <input name="cert_note" class="input" style="width:14rem" maxlength="255" placeholder="Reason if rejecting">
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
