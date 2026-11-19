@props(['profile'])
@php($c = $profile->user)
<a href="{{ route('caregivers.show', $c) }}" class="card block hover:border-teal-400 hover:shadow-md transition">
    <div class="flex items-start gap-4">
        @if($profile->photoUrl())
            <img src="{{ $profile->photoUrl() }}" alt="" class="h-20 w-20 rounded-2xl object-cover shrink-0" loading="lazy">
        @else
            <div class="h-20 w-20 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center text-2xl font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
        @endif
        <div class="min-w-0">
            <div class="font-bold text-slate-900">{{ $c->publicName() }} <span class="badge bg-emerald-100 text-emerald-800">✓ Verified</span></div>
            <div class="text-sm text-slate-600">{{ $profile->headline }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ $profile->town ? $profile->town.', ' : '' }}{{ $profile->county }} · {{ $profile->years_experience ?? 0 }} yrs experience</div>
            <div class="mt-1"><x-stars :avg="$profile->rating_avg" :count="$profile->rating_count"/></div>
        </div>
    </div>
    @php($earned = $profile->relationLoaded('skills') ? $profile->skills->filter->isEarned() : collect())
    @if($earned->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-1">
            @foreach($earned->take(3) as $s)<span class="skill-badge {{ $s->isCertified() ? 'skill-cert' : 'skill-tested' }}">{{ $s->isCertified() ? '🎓' : '✓' }} {{ $s->label() }}</span>@endforeach
            @if($earned->count() > 3)<span class="skill-badge skill-declared">+{{ $earned->count() - 3 }}</span>@endif
        </div>
    @endif
    <div class="mt-3 flex flex-wrap gap-1">
        @foreach(array_slice($profile->careTypeLabels(), 0, 3) as $l)<span class="badge bg-slate-100 text-slate-700">{{ $l }}</span>@endforeach
    </div>
    <div class="mt-3 text-sm"><b>{{ $profile->payLabel() }}</b> · {{ implode(', ', $profile->arrangementLabels()) }}</div>
</a>
