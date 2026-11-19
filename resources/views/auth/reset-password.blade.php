@extends('layouts.app', ['title' => 'Choose a new password'])
@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <h1 class="text-2xl font-bold">Choose a new password</h1>
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-field name="email" label="Email" type="email" :value="$email" required/>
            <x-field name="password" label="New password" type="password" required autocomplete="new-password"/>
            <x-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password"/>
            <button class="btn btn-primary w-full">Reset password</button>
        </form>
    </div>
</div>
@endsection
