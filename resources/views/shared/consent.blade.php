@extends('layouts.app', ['title' => 'Terms & declarations'])
@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="card">
        <h1 class="text-2xl font-bold">Before you continue</h1>
        <p class="text-sm text-slate-600 mt-1">To protect nurses, facilities and patients, please confirm the statements below. We keep a record of what you agreed to and when.</p>
        <form method="POST" action="{{ route('consent.store') }}" class="mt-6 space-y-4">
            @csrf
            @foreach($items as $type => $text)
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm leading-relaxed">
                    <input type="checkbox" name="consent[{{ $type }}]" value="1" class="mt-1 rounded" @checked(old('consent.'.$type)) required>
                    <span>{{ $text }}@if($type === 'terms') <a href="{{ route('terms') }}" target="_blank" class="link">Read the terms</a>@endif</span>
                </label>
            @endforeach
            <button class="btn btn-primary w-full">I agree and continue</button>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">@csrf<button class="text-sm text-slate-500 hover:underline">Not now, log me out</button></form>
    </div>
</div>
@endsection