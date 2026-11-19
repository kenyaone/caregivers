@extends('layouts.app', ['title' => 'Users'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Users</h1>
    <form class="mt-4 flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" class="input max-w-xs" placeholder="Name, email or phone">
        <select name="role" class="input max-w-[10rem]"><option value="">All roles</option>@foreach(['caregiver', 'family', 'admin'] as $r)<option @selected(request('role') === $r)>{{ $r }}</option>@endforeach</select>
        <button class="btn btn-primary">Filter</button>
    </form>
    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead><tr><th>Name</th><th>Role</th><th>Contact</th><th>Verification</th><th>Joined</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr class="{{ $u->is_active ? '' : 'opacity-50' }}">
                    <td>@if(! $u->isAdmin())<a href="{{ route('admin.verifications.show', $u) }}" class="link">{{ $u->name }}</a>@else{{ $u->name }}@endif</td>
                    <td>{{ $u->role }}</td><td class="text-xs">{{ $u->email }}<br>{{ $u->phone }}</td>
                    <td>@if($u->profile())<x-status :value="$u->profile()->verification_status"/>@endif</td>
                    <td class="text-xs">{{ $u->created_at->format('j M Y') }}</td>
                    <td>@if($u->id !== auth()->id())<form method="POST" action="{{ route('admin.users.toggle', $u) }}">@csrf<button class="link text-xs">{{ $u->is_active ? 'Suspend' : 'Reactivate' }}</button></form>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $users])
</div>
@endsection
