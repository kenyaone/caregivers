@extends('layouts.app', ['title' => 'Care jobs'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3"><h1 class="text-3xl font-bold">Care jobs</h1>@if(auth()->user()?->isFamily())<a href="{{ route('family.jobs.create') }}" class="btn btn-primary">+ Post a job</a>@endif</div>
    <form method="GET" class="card mt-6 grid gap-3 md:grid-cols-4 items-end">
        <x-select name="care_type" label="Type of care" :options="config('noc.care_types')" :value="request('care_type')" placeholder="Any care"/>
        <x-select name="arrangement" label="Arrangement" :options="config('noc.arrangements')" :value="request('arrangement')" placeholder="Any"/>
        <x-select name="county" label="County" :options="config('noc.counties')" :value="request('county')" placeholder="All counties"/>
        <div class="flex gap-2"><button class="btn btn-primary flex-1">Search</button><a href="{{ route('jobs.index') }}" class="btn btn-ghost">Clear</a></div>
    </form>
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @forelse($jobs as $job)<x-job-card :job="$job"/>@empty<div class="card text-center text-slate-500" style="grid-column:1/-1">No open care jobs match your search right now.</div>@endforelse
    </div>
    @include('partials.pagination', ['paginator' => $jobs])
</div>
@endsection
