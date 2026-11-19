@extends('layouts.app', ['title' => 'Create account'])
@section('content')
@php($r = old('role', $role))
<div class="max-w-lg mx-auto px-4 py-12">
    <div class="card">
        <h1 class="text-2xl font-bold">Create your account</h1>
        <div class="mt-5 grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
            <a href="{{ route('register') }}" class="rounded-lg py-2 text-center {{ $r === 'family' ? 'bg-white shadow text-teal-800' : 'text-slate-600' }}">I need a caregiver</a>
            <a href="{{ route('register', ['as' => 'caregiver']) }}" class="rounded-lg py-2 text-center {{ $r === 'caregiver' ? 'bg-white shadow text-teal-800' : 'text-slate-600' }}">I'm a caregiver</a>
        </div>
        <p class="text-sm text-slate-500 mt-4">
            @if($r === 'caregiver') Get verified once, then families near you can find you, and you can apply for care jobs. @else Browse verified caregivers and post care jobs for free. Pay only when you want a caregiver's phone number. @endif
        </p>
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="role" value="{{ $r }}">
            <x-field name="name" :label="$r === 'caregiver' ? 'Full name (as on your ID)' : 'Your full name'" required autocomplete="name"/>
            <x-field name="email" label="Email" type="email" required autocomplete="email"/>
            <x-field name="phone" label="Phone (M-Pesa / calls)" type="tel" placeholder="0712 345 678" required autocomplete="tel"/>
            <x-field name="password" label="Password" type="password" required autocomplete="new-password" help="At least 8 characters."/>
            <x-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password"/>
            <div class="space-y-3 rounded-xl bg-slate-50 p-4">
                <div class="text-sm font-semibold">Please confirm (we keep a record of this)</div>
                @foreach(array_merge(['terms', 'data'], $r === 'caregiver' ? ['caregiver_declaration', 'caregiver_independent'] : ['family_declaration']) as $type)
                    <label class="flex items-start gap-2 text-xs leading-relaxed text-slate-700">
                        <input type="checkbox" name="consent[{{ $type }}]" value="1" class="mt-0.5 rounded" @checked(old('consent.'.$type)) required>
                        <span>{{ str_replace(':share', \App\Models\Consent::shareText($r), config('noc.consents.'.$type)) }}@if($type === 'terms') <a href="{{ route('terms') }}" class="link" target="_blank">Read them</a>@endif</span>
                    </label>
                @endforeach
            </div>
            <button class="btn btn-primary w-full">Create account</button>
        </form>
        <p class="text-sm text-center mt-6 text-slate-600">Already registered? <a href="{{ route('login') }}" class="link">Log in</a></p>
    </div>
</div>
@endsection
