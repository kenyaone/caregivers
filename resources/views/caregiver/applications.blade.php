@extends('layouts.app', ['title' => 'My applications'])
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between"><h1 class="text-2xl font-bold">My job applications</h1><a href="{{ route('jobs.index') }}" class="btn btn-primary">Find jobs</a></div>
    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead><tr><th>Job</th><th>Location</th><th>Pay</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($applications as $a)
                <tr>
                    <td><a class="link" href="{{ route('jobs.show', $a->job) }}">{{ $a->job->title }}</a><div class="text-xs text-slate-500">{{ $a->job->careTypeLabel() }}</div></td>
                    <td>{{ $a->job->town }}, {{ $a->job->county }}</td>
                    <td class="whitespace-nowrap">{{ $a->job->payLabel() }}</td>
                    <td><x-status :value="$a->status"/></td>
                    <td>@if(in_array($a->status, ['applied', 'shortlisted']))<form method="POST" action="{{ route('caregiver.applications.withdraw', $a) }}">@csrf<button class="link text-xs">Withdraw</button></form>@endif</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-slate-500">You haven't applied for any jobs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $applications])
</div>
@endsection
