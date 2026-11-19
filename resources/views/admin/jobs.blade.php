@extends('layouts.app', ['title' => 'Care jobs'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">All care jobs</h1>
    <div class="mt-4 flex flex-wrap gap-2">@foreach(['' => 'All', 'open' => 'Open', 'filled' => 'Filled', 'closed' => 'Closed'] as $k => $l)<a href="{{ route('admin.jobs', array_filter(['status' => $k])) }}" class="btn btn-sm {{ request('status', '') === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $l }}</a>@endforeach</div>
    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead><tr><th>Job</th><th>Family</th><th>Location</th><th>Pay</th><th>Applicants</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($jobs as $j)
                <tr><td><a class="link" href="{{ route('jobs.show', $j) }}">{{ $j->title }}</a><div class="text-xs text-slate-500">{{ $j->careTypeLabel() }}</div></td><td>{{ $j->family->name }}</td><td>{{ $j->town }}, {{ $j->county }}</td><td class="whitespace-nowrap">{{ $j->payLabel() }}</td><td>{{ $j->applications_count }}</td><td><x-status :value="$j->status"/></td></tr>
            @empty<tr><td colspan="6" class="text-slate-500">No jobs.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $jobs])
</div>
@endsection
