@extends('layouts.app', ['title' => 'Verifications'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Verifications</h1>
    <div class="mt-4 flex gap-2 text-sm">@foreach(['pending', 'approved', 'rejected', 'incomplete'] as $s)<a href="{{ route('admin.verifications', ['status' => $s]) }}" class="btn btn-sm {{ $status === $s ? 'btn-primary' : 'btn-ghost' }}">{{ ucfirst($s) }}</a>@endforeach</div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-x-auto">
            <h2 class="font-semibold">Caregivers ({{ $caregivers->count() }})</h2>
            <table class="table mt-3"><tbody>
                @forelse($caregivers as $p)<tr><td><a class="link" href="{{ route('admin.verifications.show', $p->user) }}">{{ $p->user->name }}</a><div class="text-xs text-slate-500">{{ $p->headline }} · {{ $p->county }}</div></td><td class="text-xs text-slate-500">{{ $p->updated_at->diffForHumans() }}</td></tr>@empty<tr><td class="text-slate-500">None.</td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="card overflow-x-auto">
            <h2 class="font-semibold">Families: ID badge ({{ $families->count() }})</h2>
            <table class="table mt-3"><tbody>
                @forelse($families as $p)<tr><td><a class="link" href="{{ route('admin.verifications.show', $p->user) }}">{{ $p->user->name }}</a><div class="text-xs text-slate-500">{{ $p->town }}, {{ $p->county }}</div></td><td class="text-xs text-slate-500">{{ $p->updated_at->diffForHumans() }}</td></tr>@empty<tr><td class="text-slate-500">None.</td></tr>@endforelse
            </tbody></table>
        </div>
    </div>
</div>
@endsection
