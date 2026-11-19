@props(['value'])
@php($map = [
    'approved' => 'bg-emerald-100 text-emerald-800', 'paid' => 'bg-emerald-100 text-emerald-800', 'open' => 'bg-emerald-100 text-emerald-800', 'accepted' => 'bg-emerald-100 text-emerald-800', 'hired' => 'bg-emerald-100 text-emerald-800',
    'pending' => 'bg-amber-100 text-amber-800', 'applied' => 'bg-amber-100 text-amber-800', 'shortlisted' => 'bg-sky-100 text-sky-800', 'filled' => 'bg-sky-100 text-sky-800',
    'rejected' => 'bg-red-100 text-red-700', 'declined' => 'bg-red-100 text-red-700', 'failed' => 'bg-red-100 text-red-700',
])
<span {{ $attributes->merge(['class' => 'badge '.($map[$value] ?? 'bg-slate-100 text-slate-700')]) }}>{{ str_replace('_', ' ', ucfirst($value)) }}</span>
