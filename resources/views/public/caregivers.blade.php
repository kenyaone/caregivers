@extends('layouts.app', ['title' => 'Find a caregiver'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold">Find a verified caregiver</h1>
    <p class="text-slate-600 mt-1">Every caregiver here had their ID matched to a selfie, a recent police Good Conduct certificate checked and a referee called. Look for &#10003; tested and &#127891; certificate-checked skills.</p>
    <form method="GET" class="card mt-6 grid gap-3 md:grid-cols-5 items-end">
        <x-select name="care_type" label="Type of care" :options="config('noc.care_types')" :value="request('care_type')" placeholder="Any care"/>
        <x-select name="arrangement" label="Arrangement" :options="config('noc.arrangements')" :value="request('arrangement')" placeholder="Any"/>
        <x-select name="county" label="County" :options="config('noc.counties')" :value="request('county')" placeholder="All counties"/>
        <x-select name="skill" label="Tested skill" :options="collect(config('skills.skills'))->map(fn ($s) => $s['label'])->all()" :value="request('skill')" placeholder="Any"/>
        <div class="flex gap-2"><button class="btn btn-primary flex-1">Search</button><a href="{{ route('caregivers.index') }}" class="btn btn-ghost">Clear</a></div>
    </form>
    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($caregivers as $p)
            <x-caregiver-card :profile="$p"/>
        @empty
            <div class="card text-center text-slate-500" style="grid-column:1/-1">No verified caregivers match yet.@if($suggestTitle) Here are the closest matches.@endif Or <a class="link" href="{{ auth()->check() ? route('family.jobs.create') : route('register') }}">post a free care job</a> and let caregivers come to you.</div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $caregivers])
    @if($suggestTitle)
        <h2 class="text-xl font-bold mt-8">{{ $suggestTitle }}</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">@foreach($suggestions as $p)<x-caregiver-card :profile="$p"/>@endforeach</div>
    @endif
</div>
@endsection
