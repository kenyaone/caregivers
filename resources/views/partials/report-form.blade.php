{{-- Usage: @include('partials.report-form', ['target' => $user, 'job' => $job ?? null, 'label' => 'Report this caregiver']) --}}
@auth
@if(auth()->id() !== $target->id && ! auth()->user()->isAdmin())
<details class="cg-report mt-6">
    <summary>&#9873; {{ $label }}</summary>
    @if(! empty($alreadyReported))
        <p class="text-sm text-slate-600 mt-3">You already reported this account. Our team is looking into it.</p>
    @else
    <form method="POST" action="{{ route('reports.store', $target) }}" class="mt-3 space-y-3">
        @csrf
        @if(! empty($job))<input type="hidden" name="care_job_id" value="{{ $job->id }}">@endif
        <x-select name="reason" label="What happened?" :options="config('skills.report_reasons')" required/>
        <x-textarea name="details" label="Details" rows="3" placeholder="What happened, when, and anything else we should know. We keep reports confidential." required/>
        <p class="text-xs text-slate-500">If anyone is in danger right now, call 999 or 112 first.</p>
        <button class="btn btn-ghost btn-sm" style="color:#b91c1c;border-color:#fecaca">Send report</button>
    </form>
    @endif
</details>
@endif
@endauth
