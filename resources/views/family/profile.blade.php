@extends('layouts.app', ['title' => 'My profile'])
@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">My profile</h1>
    <form method="POST" action="{{ route('family.profile') }}" class="card mt-6 grid gap-4 md:grid-cols-2">
        @csrf @method('PUT')
        <x-field name="name" label="Full name" :value="$user->name" required/>
        <x-field name="phone" label="Phone" :value="$user->phone ? '0'.substr($user->phone, 3) : ''" required help="Shared with caregivers you unlock or send requests to."/>
        <x-select name="county" label="County" :options="config('noc.counties')" :value="$profile->county" required/>
        <x-field name="town" label="Town / estate" :value="$profile->town" required/>
        <div class="md:col-span-2"><x-textarea name="about" label="About your family (optional)" :value="$profile->about" rows="3" placeholder="Who needs care, household, anything a caregiver should know"/></div>
        <div class="md:col-span-2 flex gap-3"><button class="btn btn-primary">Save</button><a href="{{ route('documents.index') }}" class="btn btn-ghost">Upload ID for the verified badge →</a></div>
    </form>
</div>
@endsection
