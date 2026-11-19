@extends('layouts.app', ['title' => 'Payments'])
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Payments</h1>
    <div class="mt-4 flex gap-2">@foreach(['' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $k => $l)<a href="{{ route('admin.payments', array_filter(['status' => $k])) }}" class="btn btn-sm {{ request('status', '') === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $l }}</a>@endforeach</div>
    <div class="card mt-6 overflow-x-auto">
        <p class="text-xs text-slate-500 mb-3">Before confirming, check the M-Pesa code and amount on your till statement.</p>
        <table class="table">
            <thead><tr><th>Date</th><th>User</th><th>Option</th><th>Amount</th><th>M-Pesa code</th><th>Phone</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($payments as $p)
                <tr>
                    <td class="whitespace-nowrap">{{ $p->created_at->format('j M H:i') }}</td><td>{{ $p->user->name }}<div class="text-xs text-slate-500">{{ $p->user->role }}</div></td><td>{{ $p->plan->name }}</td><td>{{ number_format($p->amount) }}</td>
                    <td class="font-mono">{{ $p->reference ?: '—' }}</td><td>{{ $p->phone }}</td><td><x-status :value="$p->status"/></td>
                    <td class="whitespace-nowrap">@if($p->status === 'pending')<form method="POST" action="{{ route('admin.payments.confirm', $p) }}" class="inline">@csrf<button name="decision" value="confirm" class="btn btn-primary btn-sm">Confirm</button> <button name="decision" value="reject" class="btn btn-ghost btn-sm" onclick="return confirm('Reject this payment?')">Reject</button></form>@endif</td>
                </tr>
            @empty<tr><td colspan="8" class="text-slate-500">No payments.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $payments])
</div>
@endsection
