@extends('layouts.app', ['title' => 'Plans'])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Plans & prices</h1>
    <p class="text-sm text-slate-600 mt-1">Caregiver subscriptions are the only paid plans. Families use CareGivers254 free.</p>
    <div class="mt-6 space-y-4">
        @foreach($plans->where('audience', 'caregiver')->push(new \App\Models\Plan(['is_active' => true, 'audience' => 'caregiver'])) as $plan)
            <form method="POST" action="{{ route('admin.plans.save', $plan->exists ? $plan : null) }}" class="card grid gap-3 md:grid-cols-7 items-end">
                @csrf
                <div class="md:col-span-7 text-xs font-semibold uppercase text-slate-500">{{ $plan->exists ? 'Edit plan' : 'Add a new plan' }}</div>
                <div><label class="label">For</label><select name="audience" class="input"><option value="caregiver" @selected($plan->audience === 'caregiver')>Caregivers</option><option value="family" @selected($plan->audience === 'family')>Families</option></select></div>
                <div><label class="label">Name</label><input name="name" value="{{ $plan->name }}" class="input" required></div>
                <div><label class="label">Price (KES)</label><input type="number" name="price" min="1" value="{{ $plan->price }}" class="input" required></div>
                <div><label class="label">Days</label><input type="number" name="duration_days" min="1" value="{{ $plan->duration_days }}" class="input" required></div>
                <div><label class="label">Credits</label><input type="number" name="credits" min="1" value="{{ $plan->credits }}" class="input" placeholder="families"></div>
                <div><label class="label">Description</label><input name="description" value="{{ $plan->description }}" class="input"></div>
                <div class="flex items-center gap-3"><label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_active" value="1" class="rounded" @checked($plan->is_active)> Active</label><button class="btn btn-primary btn-sm">Save</button></div>
            </form>
        @endforeach
    </div>
</div>
@endsection
