@extends('layouts.app', ['title' => $caregiver->publicName().' · Caregiver'])
@section('content')
@php($viewer = auth()->user())
<div class="max-w-4xl mx-auto px-4 py-10">
    <a href="{{ route('caregivers.index') }}" class="link text-sm">← All caregivers</a>
    <div class="card mt-4">
        <div class="flex flex-wrap items-start gap-5">
            @if($profile->photoUrl())
                <img src="{{ $profile->photoUrl() }}" alt="{{ $caregiver->publicName() }}" class="h-32 w-32 rounded-2xl object-cover">
            @else
                <div class="h-32 w-32 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center text-4xl font-bold">{{ mb_substr($caregiver->name, 0, 1) }}</div>
            @endif
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold">{{ $unlocked ? $caregiver->name : $caregiver->publicName() }}</h1>
                <div class="text-slate-600">{{ $profile->headline }}</div>
                <div class="text-sm text-slate-500 mt-1">{{ $profile->town ? $profile->town.', ' : '' }}{{ $profile->county }}@if($profile->age) · {{ $profile->age }} yrs old @endif @if($profile->gender) · {{ ucfirst($profile->gender) }} @endif</div>
                <div class="mt-2 flex flex-wrap gap-2 items-center">
                    @if($profile->verification_status === 'approved')<span class="badge bg-emerald-100 text-emerald-800">✓ ID & Good Conduct verified</span>@endif
                    <x-stars :avg="$profile->rating_avg" :count="$profile->rating_count"/>
                </div>
            </div>
            <div class="text-right"><div class="text-lg font-bold">{{ $profile->payLabel() }}</div><div class="text-xs text-slate-500">expected, agree directly</div></div>
        </div>

        <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-slate-500">Care offered</dt><dd class="font-semibold">{{ implode(', ', $profile->careTypeLabels()) }}</dd></div>
            <div><dt class="text-slate-500">Available for</dt><dd class="font-semibold">{{ implode(', ', $profile->arrangementLabels()) }}</dd></div>
            <div><dt class="text-slate-500">Experience</dt><dd class="font-semibold">{{ $profile->years_experience ?? 0 }} years</dd></div>
            <div><dt class="text-slate-500">Languages</dt><dd class="font-semibold">{{ $profile->languages ?: '—' }}</dd></div>
            @if($profile->training)<div class="sm:col-span-2"><dt class="text-slate-500">Training</dt><dd class="font-semibold">{{ $profile->training }}</dd></div>@endif
        </dl>
        @if($profile->bio)<div class="mt-6"><h2 class="font-semibold">About me</h2><p class="mt-1 whitespace-pre-line text-slate-700">{{ $profile->bio }}</p></div>@endif

        <div class="mt-8 border-t pt-6">
            @if($unlocked)
                <div class="alert alert-success">
                    <b>Contact:</b> <a class="link" href="tel:+{{ $caregiver->phone }}">+{{ $caregiver->phone }}</a> · {{ $caregiver->email }}
                    <div class="text-xs mt-1">Meet in person, check the original ID and Good Conduct certificate, and call the references before agreeing terms.</div>
                </div>
                @if($viewer?->isFamily())
                    <div class="mt-3 text-sm">Documents: @foreach($caregiver->documents->where('type', '!=', 'national_id') as $d)<a class="link" target="_blank" href="{{ route('documents.download', $d) }}">{{ $d->label() }}</a>@if(! $loop->last), @endif @endforeach</div>
                @endif
            @elseif($viewer?->isFamily())
                <div class="grid gap-4 md:grid-cols-2">
                    <form method="POST" action="{{ route('family.unlock', $caregiver) }}" class="rounded-xl bg-teal-50 p-4">
                        @csrf
                        <div class="font-semibold">Get this caregiver's phone number</div>
                        <p class="text-sm text-slate-600 mt-1">Uses 1 contact credit. You have <b>{{ $credits }}</b> left.</p>
                        @if($credits)
                            <button class="btn btn-primary mt-3" onclick="return confirm('Use 1 credit to unlock this contact?')">Unlock contact</button>
                        @else
                            <a href="{{ route('payments.index') }}" class="btn btn-primary mt-3">Buy a contact pass</a>
                        @endif
                    </form>
                    <form method="POST" action="{{ route('family.request', $caregiver) }}" class="rounded-xl bg-slate-50 p-4 space-y-3">
                        @csrf
                        <div class="font-semibold">Or send a free care request</div>
                        <div class="grid grid-cols-2 gap-2">
                            <x-select name="care_type" label="Care" :options="array_intersect_key(config('noc.care_types'), array_flip($profile->care_types ?? []))" :placeholder="false"/>
                            <x-select name="arrangement" label="Arrangement" :options="array_intersect_key(config('noc.arrangements'), array_flip($profile->arrangements ?? []))" :placeholder="false"/>
                        </div>
                        <x-field name="start_date" label="Start date (optional)" type="date"/>
                        <x-textarea name="message" label="Describe who needs care" rows="3" placeholder="e.g. My mother (78) had a stroke and needs help with bathing, meals and exercises. Live-in, Kileleshwa." required/>
                        <button class="btn btn-ghost">Send request</button>
                    </form>
                </div>
            @elseif(! $viewer)
                <a href="{{ route('register') }}" class="btn btn-primary">Create a free family account to contact {{ $caregiver->publicName() }}</a>
                <span class="text-sm text-slate-500 ml-2">Already registered? <a href="{{ route('login') }}" class="link">Log in</a></span>
            @endif
        </div>

        @if($canReview)
            <form method="POST" action="{{ route('family.review', $caregiver) }}" class="mt-6 border-t pt-6 flex flex-wrap items-end gap-3">
                @csrf
                <div><label class="label">Your rating</label><select name="stars" class="input w-32">@for($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ str_repeat('★', $i) }}</option>@endfor</select></div>
                <div class="flex-1 min-w-48"><label class="label">Review</label><input name="comment" class="input" maxlength="1000" placeholder="Reliable? Kind? Would you recommend them?"></div>
                <button class="btn btn-primary btn-sm">Submit review</button>
            </form>
        @endif

        @if($reviews->count())
            <h2 class="font-semibold mt-8">Reviews from families</h2>
            <div class="mt-2 space-y-2 text-sm">@foreach($reviews as $r)<div class="rounded-lg bg-slate-50 p-3"><span class="text-amber-500">{{ str_repeat('★', $r->stars) }}</span> {{ $r->comment }} <span class="text-xs text-slate-500">· {{ $r->family->publicName() }}, {{ $r->created_at->format('M Y') }}</span></div>@endforeach</div>
        @endif
    </div>
</div>
@endsection
