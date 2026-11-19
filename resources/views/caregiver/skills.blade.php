@extends('layouts.app', ['title' => 'My skills'])
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold">My skills & badges</h1>
    <p class="text-slate-600 mt-1">Families trust badges they can check. Tick the skills you have, then earn badges by passing a short quiz (10 questions, pass mark {{ config('skills.pass_mark') }}) or by uploading a certificate for our team to check. Caregivers with more badges show higher in search.</p>
    <div class="mt-3 flex flex-wrap gap-2 text-xs">
        <span class="skill-badge skill-cert">🎓 Certificate checked</span>
        <span class="skill-badge skill-tested">✓ Tested</span>
        <span class="skill-badge skill-declared">Self-declared</span>
    </div>

    <form method="POST" action="{{ route('caregiver.skills.declare') }}" class="mt-6 grid gap-4 md:grid-cols-2">
        @csrf
        @foreach($skills as $key => $def)
            @php($row = $mine->get($key))
            <div class="card">
                <div class="flex items-start justify-between gap-3">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="skills[]" value="{{ $key }}" class="mt-1 rounded" @checked($row?->declared) @disabled($row?->isEarned())>
                        @if($row?->isEarned())<input type="hidden" name="skills[]" value="{{ $key }}">@endif
                        <span><b>{{ $def['label'] }}</b><span class="block text-sm text-slate-500">{{ $def['blurb'] }}</span></span>
                    </label>
                    @if($row?->isCertified())<span class="skill-badge skill-cert">🎓 Checked</span>
                    @elseif($row?->isTested())<span class="skill-badge skill-tested">✓ Tested</span>
                    @elseif($row?->declared)<span class="skill-badge skill-declared">Declared</span>@endif
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                    @if($row?->isTested())
                        <span class="text-emerald-700">Quiz passed {{ $row->quiz_passed_at->format('j M Y') }} ({{ $row->quiz_score }}/10)</span>
                    @elseif($waits->has($key))
                        <span class="text-slate-500">Retake the quiz {{ $waits[$key]->diffForHumans() }}</span>
                    @else
                        <a href="{{ route('caregiver.skills.quiz', $key) }}" class="btn btn-primary btn-sm">Take the quiz</a>
                    @endif
                    @if($row?->cert_status === 'pending')
                        <span class="text-amber-700">Certificate being checked</span>
                    @elseif($row?->cert_status === 'rejected')
                        <span class="text-red-600">Certificate not accepted: {{ $row->cert_note }}</span>
                    @endif
                    @if(! $row?->isCertified() && $row?->cert_status !== 'pending')
                        <button type="button" class="btn btn-ghost btn-sm" onclick="var f=document.getElementById('cert-skill');f.value='{{ $key }}';document.getElementById('cert-box').scrollIntoView({behavior:'smooth'})">Upload certificate</button>
                    @endif
                </div>
            </div>
        @endforeach
        <div style="grid-column:1/-1"><button class="btn btn-primary">Save my skills</button></div>
    </form>

    <form id="cert-box" method="POST" action="{{ route('caregiver.skills.certificate') }}" enctype="multipart/form-data" class="card mt-8 grid gap-4 md:grid-cols-3 items-end">
        @csrf
        <div class="md:col-span-3"><h2 class="font-semibold">Upload a certificate</h2><p class="text-sm text-slate-600">For example a First Aid certificate from St John Ambulance or the Red Cross, or a course certificate in dementia, palliative or child care. PDF, JPG or PNG, max 5 MB.</p></div>
        <div><label class="label" for="cert-skill">Skill</label>
            <select id="cert-skill" name="skill" class="input" required>@foreach($skills as $key => $def)<option value="{{ $key }}">{{ $def['label'] }}</option>@endforeach</select></div>
        <div><label class="label" for="cert-file">Certificate</label><input id="cert-file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm" required></div>
        <button class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection
