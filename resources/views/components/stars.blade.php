@props(['avg' => 0, 'count' => 0])
@if($count)
<span class="text-amber-500 whitespace-nowrap" title="{{ $avg }} from {{ $count }} ratings">★ {{ number_format($avg, 1) }} <span class="text-slate-500 text-xs">({{ $count }})</span></span>
@else
<span class="text-slate-400 text-xs">No ratings yet</span>
@endif
