@extends('layouts.app', ['title' => 'My care jobs'])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between"><h1 class="text-2xl font-bold">My care jobs</h1><a href="{{ route('family.jobs.create') }}" class="btn btn-primary">+ Post a job</a></div>
    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead><tr><th>Job</th><th>Location</th><th>Pay</th><th>Applicants</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($jobs as $j)
                <tr><td><a class="link" href="{{ route('family.jobs.show', $j) }}">{{ $j->title }}</a><div class="text-xs text-slate-500">{{ $j->careTypeLabel() }} · {{ $j->arrangementLabel() }}</div></td><td>{{ $j->town }}</td><td class="whitespace-nowrap">{{ $j->payLabel() }}</td><td>{{ $j->applications_count }}</td><td><x-status :value="$j->status"/></td></tr>
            @empty
                <tr><td colspan="5" class="text-slate-500">No jobs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $jobs])
</div>
@endsection
