@props(['job'])
<a href="{{ route('jobs.show', $job) }}" class="card block hover:border-teal-400 hover:shadow-md transition">
    <div class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ $job->careTypeLabel() }} · {{ $job->arrangementLabel() }}</div>
    <h3 class="font-bold text-slate-900 mt-1">{{ $job->title }}</h3>
    <div class="text-sm text-slate-600 mt-0.5">{{ $job->town }}, {{ $job->county }}@if($job->start_date) · starts {{ $job->start_date->format('j M') }}@endif</div>
    <div class="mt-3 font-bold text-slate-900">{{ $job->payLabel() }}</div>
</a>
