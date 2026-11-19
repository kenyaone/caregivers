@extends('layouts.app', ['title' => 'Reset password'])
@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <h1 class="text-2xl font-bold">Forgot your password?</h1>
        <p class="text-sm text-slate-500 mt-1">Enter your email and we'll send you a reset link.</p>
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <x-field name="email" label="Email" type="email" required autofocus/>
            <button class="btn btn-primary w-full">Email reset link</button>
        </form>
    </div>
</div>
@endsection
