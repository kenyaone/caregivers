@extends('layouts.app', ['title' => $job->exists ? 'Edit care job' : 'Post a care job'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">{{ $job->exists ? 'Edit care job' : 'Post a care job (free)' }}</h1>
    <p class="text-sm text-slate-600 mt-1">Verified caregivers who offer this care in your county are alerted straight away.</p>
    <form method="POST" action="{{ $job->exists ? route('family.jobs.update', $job) : route('family.jobs.store') }}" class="card mt-6 grid gap-4 md:grid-cols-2">
        @csrf @if($job->exists) @method('PUT') @endif
        <div class="md:col-span-2"><x-field name="title" label="Job title" :value="$job->title" placeholder="e.g. Live-in caregiver for my mother after a stroke" required/></div>
        <x-select name="care_type" label="Type of care" :options="config('noc.care_types')" :value="$job->care_type" required/>
        <x-select name="arrangement" label="Arrangement" :options="config('noc.arrangements')" :value="$job->arrangement" :placeholder="false"/>
        <div class="md:col-span-2"><x-field name="care_recipient" label="Who needs care" :value="$job->care_recipient" placeholder="e.g. My father, 82, uses a wheelchair, has diabetes" required/></div>
        <x-select name="county" label="County" :options="config('noc.counties')" :value="$job->county" required/>
        <x-field name="town" label="Town / estate" :value="$job->town" required/>
        <x-field name="start_date" label="Start date" type="date" :value="$job->start_date?->format('Y-m-d')"/>
        <div></div>
        <x-field name="pay_amount" label="Pay offered (KES)" type="number" min="1" :value="$job->pay_amount"/>
        <x-select name="pay_unit" label="Pay is" :options="config('noc.pay_units')" :value="$job->pay_unit" :placeholder="false"/>
        <div class="md:col-span-2"><x-textarea name="description" label="Duties" :value="$job->description" rows="4" placeholder="Daily duties, hours, days off, whether meals and accommodation are provided" required/></div>
        <div class="md:col-span-2"><x-textarea name="requirements" label="Requirements (optional)" :value="$job->requirements" rows="2" placeholder="e.g. Experience with dementia, speaks Kikuyu, non-smoker"/></div>
        @unless($job->exists)
            <label class="md:col-span-2 flex items-start gap-2 text-sm text-slate-700"><input type="checkbox" name="confirm" value="1" class="mt-1 rounded" required> <span>{{ config('noc.consents.job_post') }}</span></label>
        @endunless
        <div class="md:col-span-2 flex gap-3"><button class="btn btn-primary">{{ $job->exists ? 'Save changes' : 'Post job' }}</button><a href="{{ route('family.jobs.index') }}" class="btn btn-ghost">Cancel</a></div>
    </form>
</div>
@endsection
