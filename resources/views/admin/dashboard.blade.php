@extends('layouts.app', ['title' => 'Admin'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">CareGivers254 admin</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.verifications') }}" class="card hover:border-teal-400 flex items-center justify-between"><span class="font-semibold">Verifications waiting</span><span class="text-3xl font-bold {{ $pendingVerifications ? 'text-amber-600' : '' }}">{{ $pendingVerifications }}</span></a>
        <a href="{{ route('admin.payments', ['status' => 'pending']) }}" class="card hover:border-teal-400 flex items-center justify-between"><span class="font-semibold">Payments to confirm</span><span class="text-3xl font-bold {{ $pendingPayments ? 'text-amber-600' : '' }}">{{ $pendingPayments }}</span></a>
        <a href="{{ route('admin.reports') }}" class="card hover:border-teal-400 flex items-center justify-between"><span class="font-semibold">Open reports</span><span class="text-3xl font-bold {{ $openReports ? 'text-red-600' : '' }}">{{ $openReports }}</span></a>
        <a href="{{ route('admin.skills') }}" class="card hover:border-teal-400 flex items-center justify-between"><span class="font-semibold">Skill certificates to check</span><span class="text-3xl font-bold {{ $pendingCerts ? 'text-amber-600' : '' }}">{{ $pendingCerts }}</span></a>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($stats as $label => $value)<div class="card"><div class="text-sm text-slate-500">{{ $label }}</div><div class="text-2xl font-bold mt-1">{{ $value }}</div></div>@endforeach
    </div>
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.plans') }}" class="btn btn-ghost">Plans & prices</a>
        <a href="{{ route('admin.jobs') }}" class="btn btn-ghost">Care jobs</a>
        <a href="{{ route('admin.users') }}" class="btn btn-ghost">Users</a>
    </div>
</div>
@endsection
