@extends('layouts.app', ['title' => $def['label'].' quiz'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('caregiver.skills') }}" class="link text-sm">← My skills</a>
    <h1 class="text-2xl font-bold mt-3">{{ $def['label'] }} quiz</h1>
    <p class="text-slate-600 mt-1">{{ count($questions) }} questions. Get {{ $pass }} or more right to earn the "✓ Tested" badge. Take your time and answer from your own experience. If you don't pass, you can try again after {{ config('skills.retry_hours') }} hours.</p>
    <form method="POST" action="{{ route('caregiver.skills.submit', $key) }}" class="mt-6 space-y-4" onsubmit="var n=this.querySelectorAll('fieldset').length,a=this.querySelectorAll('input[type=radio]:checked').length;if(a<n){alert('Please answer all '+n+' questions.');return false;}">
        @csrf
        @foreach($questions as $i => $q)
            <fieldset class="card">
                <legend class="font-semibold">{{ $i + 1 }}. {{ $q['q'] }}</legend>
                <div class="mt-3 space-y-2">
                    @foreach($q['options'] as $o)
                        <label class="flex items-start gap-2 rounded-lg border border-slate-200 p-3 cursor-pointer hover:border-teal-400">
                            <input type="radio" name="answers[{{ $q['n'] }}]" value="{{ $o['v'] }}" class="mt-1">
                            <span>{{ $o['text'] }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <button class="btn btn-primary">Submit answers</button>
    </form>
</div>
@endsection
