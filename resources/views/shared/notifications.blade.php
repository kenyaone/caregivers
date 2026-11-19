@extends('layouts.app', ['title' => 'Alerts'])
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Alerts</h1>
    <div class="card mt-6 divide-y">
        @forelse($notifications as $n)
            <div class="py-3 {{ $n->read_at ? '' : 'bg-teal-50 -mx-5 px-5' }}">
                <div class="flex justify-between gap-3"><div class="font-semibold">{{ $n->data['title'] ?? 'Notice' }}</div><div class="text-xs text-slate-500 whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</div></div>
                <p class="text-sm text-slate-700 mt-0.5">{{ $n->data['body'] ?? '' }}</p>
                @if(! empty($n->data['url']))<a href="{{ $n->data['url'] }}" class="link text-sm">Open →</a>@endif
            </div>
        @empty
            <p class="text-sm text-slate-500">No alerts yet.</p>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $notifications])
</div>
@endsection
