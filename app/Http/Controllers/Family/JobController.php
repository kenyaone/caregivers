<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\CareJob;
use App\Models\CaregiverProfile;
use App\Models\Consent;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\PlatformNotice;
use App\Rules\NoContactDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $jobs = $request->user()->careJobs()->withCount('applications')->latest()->paginate(20);
        return view('family.jobs.index', compact('jobs'));
    }

    public function create(Request $request)
    {
        $p = $request->user()->familyProfile;
        return view('family.jobs.form', ['job' => new CareJob(['county' => $p->county, 'town' => $p->town, 'pay_unit' => 'per_month', 'arrangement' => 'live_in'])]);
    }

    public function store(Request $request)
    {
        $request->validate(['confirm' => 'accepted'], ['confirm.accepted' => 'Please confirm the job is genuine and the terms are lawful.']);
        $job = $request->user()->careJobs()->create($this->validated($request) + ['status' => 'open']);
        Consent::record($request->user(), 'job_post', $request, $job);

        // Alert listed caregivers in the same county who offer this kind of care.
        $caregivers = User::whereHas('caregiverProfile', fn ($q) => $q->listed()->where('county', $job->county)->whereJsonContains('care_types', $job->care_type))->get();
        Notification::send($caregivers, new PlatformNotice('New care job near you',
            $job->careTypeLabel().' ('.$job->arrangementLabel().') in '.$job->town.': '.$job->title.'. '.$job->payLabel().'.', route('jobs.show', $job)));

        return redirect()->route('family.jobs.show', $job)->with('status', 'Job posted. Matching caregivers in '.$job->county.' have been alerted.');
    }

    public function show(Request $request, CareJob $job)
    {
        $this->own($request, $job);
        $job->load(['applications' => fn ($q) => $q->with('caregiver.caregiverProfile')->orderByRaw("CASE status WHEN 'hired' THEN 1 WHEN 'shortlisted' THEN 2 WHEN 'applied' THEN 3 ELSE 4 END")]);
        return view('family.jobs.show', ['job' => $job]);
    }

    public function edit(Request $request, CareJob $job)
    {
        $this->own($request, $job);
        return view('family.jobs.form', compact('job'));
    }

    public function update(Request $request, CareJob $job)
    {
        $this->own($request, $job);
        $job->update($this->validated($request));
        return redirect()->route('family.jobs.show', $job)->with('status', 'Job updated.');
    }

    public function close(Request $request, CareJob $job)
    {
        $this->own($request, $job);
        $job->update(['status' => $request->input('filled') ? 'filled' : 'closed']);
        return back()->with('status', 'Job closed.');
    }

    public function decide(Request $request, JobApplication $application)
    {
        $job = $application->job;
        $this->own($request, $job);
        $data = $request->validate(['status' => 'required|in:shortlisted,hired,rejected']);
        $application->update(['status' => $data['status']]);
        if ($data['status'] === 'hired') {
            $job->update(['status' => 'filled']);
        }
        $application->caregiver->notify(new PlatformNotice(
            ['shortlisted' => 'You were shortlisted', 'hired' => 'You got the job', 'rejected' => 'Application update'][$data['status']],
            ['shortlisted' => 'The family shortlisted you for "'.$job->title.'". Expect a call soon.',
             'hired' => 'The family chose you for "'.$job->title.'". Agree the start date and terms with them directly.',
             'rejected' => 'Your application for "'.$job->title.'" was not successful this time.'][$data['status']],
            route('caregiver.applications')));
        return back()->with('status', 'Applicant updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150', new NoContactDetails],
            'care_type' => ['required', Rule::in(array_keys(config('noc.care_types')))],
            'care_recipient' => ['required', 'string', 'max:255', new NoContactDetails],
            'arrangement' => ['required', Rule::in(array_keys(config('noc.arrangements')))],
            'county' => ['required', Rule::in(config('noc.counties'))],
            'town' => 'required|string|max:80',
            'start_date' => 'nullable|date|after_or_equal:today',
            'pay_unit' => ['required', Rule::in(array_keys(config('noc.pay_units')))],
            'pay_amount' => 'nullable|required_unless:pay_unit,negotiable|integer|min:1|max:1000000',
            'description' => ['required', 'string', 'max:3000', new NoContactDetails],
            'requirements' => ['nullable', 'string', 'max:2000', new NoContactDetails],
        ], ['pay_amount.required_unless' => 'Enter the pay you are offering, or choose "negotiable".']);
        if ($data['pay_unit'] === 'negotiable') {
            $data['pay_amount'] = null;
        }
        return $data;
    }

    private function own(Request $request, CareJob $job): void
    {
        abort_unless($job->family_id === $request->user()->id, 403);
    }
}
