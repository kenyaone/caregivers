@extends('layouts.app', ['title' => $def['label'].' result'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="card text-center">
        <div style="font-size:3rem">{{ $passed ? '🎉' : '📘' }}</div>
        <h1 class="text-2xl font-bold mt-2">{{ $passed ? 'You passed!' : 'Not this time' }}</h1>
        <p class="text-lg mt-1">You scored <b>{{ $score }}/{{ $total }}</b> on the {{ $def['label'] }} quiz.</p>
        @if($passed)
            <p class="text-slate-600 mt-2">The <span class="skill-badge skill-tested">✓ {{ $def['label'] }}</span> badge now shows on your profile.</p>
        @else
            <p class="text-slate-600 mt-2">You need {{ config('skills.pass_mark') }} to pass. Review the topics below and try again in {{ $retryHours }} hours.</p>
        @endif
        <a href="{{ route('caregiver.skills') }}" class="btn btn-primary mt-4">Back to my skills</a>
    </div>
    @if($passed)
        <div class="card mt-6">
            <h2 class="font-semibold">Key points</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-700">@foreach($def['quiz'] as $q)<li>• {{ $q['why'] }}</li>@endforeach</ul>
        </div>
    @elseif($missed)
        <div class="card mt-6">
            <h2 class="font-semibold">Questions to revise</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-700">@foreach($missed as $m)<li>• {{ $m }}</li>@endforeach</ul>
        </div>
    @endif
</div>
@endsection
