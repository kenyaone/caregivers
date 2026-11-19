@extends('layouts.app', ['title' => 'Log in'])
@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <h1 class="text-2xl font-bold">Welcome back</h1>
        <p class="text-sm text-slate-500 mt-1">Log in to your CareGivers254 account.</p>
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <x-field name="email" label="Email" type="email" required autofocus autocomplete="email"/>
            <x-field name="password" label="Password" type="password" required autocomplete="current-password"/>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="remember" class="rounded"> Remember me</label>
                <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
            </div>
            <button class="btn btn-primary w-full">Log in</button>
        </form>
        <p class="text-sm text-center mt-6 text-slate-600">New here? <a href="{{ route('register') }}" class="link">Create an account</a></p>
    </div>
</div>
@endsection
