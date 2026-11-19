@extends('layouts.app', ['title' => 'My profile'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between"><h1 class="text-2xl font-bold">My caregiver profile</h1><x-status :value="$profile->verification_status"/></div>
    <form method="POST" action="{{ route('caregiver.profile') }}" enctype="multipart/form-data" class="card mt-6 grid gap-4 md:grid-cols-2">
        @csrf @method('PUT')
        <div class="md:col-span-2 flex items-center gap-4">
            @if($profile->photoUrl())<img src="{{ $profile->photoUrl() }}" alt="" class="h-20 w-20 rounded-2xl object-cover">@else<div class="h-20 w-20 rounded-2xl bg-slate-100"></div>@endif
            <div><label class="label" for="photo">Profile photo (clear photo of your face)</label><input id="photo" type="file" name="photo" accept="image/*" class="block text-sm">@error('photo')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <x-field name="name" label="Full name (as on ID)" :value="$user->name" required/>
        <x-field name="phone" label="Phone" :value="$user->phone ? '0'.substr($user->phone, 3) : ''" required/>
        <div class="md:col-span-2"><x-field name="headline" label="Headline" :value="$profile->headline" placeholder="e.g. Experienced elderly caregiver, 6 years, live-in" required/></div>
        <div class="md:col-span-2">
            <div class="label">Care I offer</div>
            <div class="grid gap-2 sm:grid-cols-2">@foreach(config('noc.care_types') as $k => $l)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="care_types[]" value="{{ $k }}" class="rounded" @checked(in_array($k, old('care_types', $profile->care_types ?? [])))> {{ $l }}</label>@endforeach</div>
            @error('care_types')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <div class="label">I'm available for</div>
            <div class="flex flex-wrap gap-4">@foreach(config('noc.arrangements') as $k => $l)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="arrangements[]" value="{{ $k }}" class="rounded" @checked(in_array($k, old('arrangements', $profile->arrangements ?? [])))> {{ $l }}</label>@endforeach</div>
            @error('arrangements')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <x-select name="county" label="County" :options="config('noc.counties')" :value="$profile->county" required/>
        <x-field name="town" label="Town / estate" :value="$profile->town"/>
        <x-select name="gender" label="Gender (optional)" :options="['female' => 'Female', 'male' => 'Male']" :value="$profile->gender" placeholder="Prefer not to say"/>
        <x-field name="age" label="Age (optional)" type="number" min="18" max="80" :value="$profile->age"/>
        <x-field name="years_experience" label="Years of experience" type="number" min="0" max="60" :value="$profile->years_experience"/>
        <x-field name="languages" label="Languages" :value="$profile->languages" placeholder="e.g. English, Kiswahili, Luhya"/>
        <div class="md:col-span-2"><x-field name="training" label="Training" :value="$profile->training" placeholder="e.g. Caregiver certificate, MTTI Eldoret (2024); First Aid"/></div>
        <x-field name="expected_pay" label="Expected pay (KES)" type="number" min="1" :value="$profile->expected_pay"/>
        <x-select name="pay_unit" label="Pay is" :options="config('noc.pay_units')" :value="$profile->pay_unit" :placeholder="false"/>
        <div class="md:col-span-2"><x-textarea name="bio" label="About me" :value="$profile->bio" rows="5" placeholder="Your experience, the kind of people you've cared for, what you're good at, and a referee's name and phone." required/></div>
        <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="available" value="0"><input type="checkbox" name="available" value="1" class="rounded" @checked(old('available', $profile->available))> I'm available for work (show me in search)</label>
        <div class="md:col-span-2 flex flex-wrap gap-3"><button class="btn btn-primary">Save profile</button><a href="{{ route('documents.index') }}" class="btn btn-ghost">Upload ID & documents →</a></div>
    </form>
</div>
@endsection
