@extends('layouts.app', ['title' => 'Training & qualifications'])
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">Training & qualifications</h1>
    <p class="text-slate-600 mt-1">List any caregiving, nursing-assistant, first-aid or other college training you have done. Upload the certificate if you have it: our team checks it and your profile shows <span class="skill-badge skill-cert">🎓 certificate checked</span>. No certificate? You can still list the course and add the certificate later.</p>

    <form method="POST" action="{{ route('caregiver.qualifications.store') }}" enctype="multipart/form-data" class="card mt-6 grid gap-4 md:grid-cols-2">
        @csrf
        <x-field name="course" label="Course" placeholder="e.g. Caregiving, Certified Nursing Assistant, Home-based care" required/>
        <x-field name="institution" label="College / institution" placeholder="e.g. the college or training centre's name" required/>
        <x-select name="level" label="Level" :options="\App\Models\Qualification::LEVELS" required/>
        <x-field name="year" label="Year completed (optional)" type="number" min="1970" :max="now()->year"/>
        <div class="md:col-span-2"><label class="label" for="q-file">Certificate (optional, PDF, JPG or PNG, max 5 MB)</label>
            <input id="q-file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm"></div>
        <div class="md:col-span-2"><button class="btn btn-primary">Add qualification</button></div>
    </form>

    <div class="card mt-6">
        <h2 class="font-semibold">Your qualifications</h2>
        <div class="mt-3 divide-y">
            @forelse($qualifications as $q)
                <div class="py-4 flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold">{{ $q->levelLabel() }} in {{ $q->course }}</div>
                        <div class="text-sm text-slate-500">{{ $q->institution }}@if($q->year) · {{ $q->year }}@endif</div>
                        <div class="mt-1 text-sm">
                            @switch($q->status)
                                @case('approved')<span class="skill-badge skill-cert">🎓 Certificate checked</span>@break
                                @case('pending')<span class="text-amber-700">Certificate being checked</span>@break
                                @case('rejected')<span class="text-red-600">Certificate not accepted: {{ $q->note }}</span>@break
                                @default<span class="text-slate-500">No certificate uploaded yet</span>
                            @endswitch
                            @if($q->document)· <a class="link" target="_blank" href="{{ route('documents.download', $q->document) }}">view file</a>@endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if(in_array($q->status, ['unverified', 'rejected']))
                            <form method="POST" action="{{ route('caregiver.qualifications.certificate', $q) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="text-sm" required>
                                <button class="btn btn-ghost btn-sm">Upload certificate</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('caregiver.qualifications.destroy', $q) }}" onsubmit="return confirm('Remove this qualification?')">@csrf @method('DELETE')<button class="text-red-600 text-sm">Remove</button></form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 py-2">Nothing listed yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
