@extends('layouts.app', ['title' => 'My requests'])
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Care requests you sent</h1>
    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead><tr><th>Caregiver</th><th>Care</th><th>Sent</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($requests as $r)
                <tr>
                    <td><a class="link" href="{{ route('caregivers.show', $r->caregiver) }}">{{ $r->caregiver->publicName() }}</a></td>
                    <td>{{ config('noc.care_types')[$r->care_type] ?? '' }}<div class="text-xs text-slate-500">{{ config('noc.arrangements')[$r->arrangement] ?? '' }}</div></td>
                    <td class="text-xs">{{ $r->created_at->diffForHumans() }}</td>
                    <td><x-status :value="$r->status"/></td>
                    <td>@if($r->status === 'accepted')<a class="link text-xs" href="{{ route('caregivers.show', $r->caregiver) }}">Unlock contact →</a>@elseif($r->status === 'pending')<form method="POST" action="{{ route('family.requests.cancel', $r) }}">@csrf<button class="link text-xs">Withdraw</button></form>@endif</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-slate-500">No requests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $requests])
</div>
@endsection
