<?php

namespace App\Http\Controllers\Caregiver;

use App\Http\Controllers\Controller;
use App\Models\CareJob;
use App\Models\CareRequest;
use App\Models\JobApplication;
use App\Models\Unlock;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CaregiverController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $profile = $user->caregiverProfile;
        return view('caregiver.dashboard', [
            'profile' => $profile,
            'subscription' => $user->activeSubscription(),
            'docTypes' => $user->documents()->pluck('type'),
            'pendingRequests' => CareRequest::where('caregiver_id', $user->id)->where('status', 'pending')->with('family.familyProfile')->latest()->get(),
            'unlocks' => Unlock::where('caregiver_id', $user->id)->count(),
            'badges' => $user->skills()->earned()->count(),
            'matchingJobs' => CareJob::open()
                ->when($profile->county, fn ($q, $v) => $q->where('county', $v))
                ->when($profile->care_types, fn ($q, $v) => $q->whereIn('care_type', $v))
                ->latest()->limit(5)->get(),
        ]);
    }

    public function editProfile(Request $request)
    {
        return view('caregiver.profile', ['user' => $request->user(), 'profile' => $request->user()->caregiverProfile]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $profile = $user->caregiverProfile;
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => ['required', 'regex:/^(\+?254|0)?[17]\d{8}$/'],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'headline' => 'required|string|max:120',
            'care_types' => 'required|array|min:1',
            'care_types.*' => Rule::in(array_keys(config('noc.care_types'))),
            'arrangements' => 'required|array|min:1',
            'arrangements.*' => Rule::in(array_keys(config('noc.arrangements'))),
            'gender' => 'nullable|in:female,male',
            'age' => 'nullable|integer|min:18|max:80',
            'county' => ['required', Rule::in(config('noc.counties'))],
            'town' => 'nullable|string|max:80',
            'years_experience' => 'nullable|integer|min:0|max:60',
            'training' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:150',
            'expected_pay' => 'nullable|integer|min:1|max:1000000',
            'pay_unit' => ['required', Rule::in(array_keys(config('noc.pay_units')))],
            'bio' => 'required|string|max:2000',
            'available' => 'boolean',
        ]);

        $user->update(['name' => $data['name'], 'phone' => User::normalisePhone($data['phone'])]);
        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        }
        unset($data['name'], $data['phone'], $data['photo']);
        $profile->fill($data + ['available' => $request->boolean('available')])->save();

        return back()->with('status', 'Profile saved.');
    }

    public function submitForVerification(Request $request)
    {
        $user = $request->user();
        $profile = $user->caregiverProfile;
        if (! $profile->isComplete()) {
            return back()->withErrors(['verify' => 'Complete your headline, care types, availability, county and "about me" first.']);
        }
        if (! $profile->photo_path) {
            return back()->withErrors(['verify' => 'Add a clear profile photo of your face first.']);
        }
        $types = $user->documents()->pluck('type');
        if (! $types->contains('national_id') || ! $types->contains('selfie') || ! $types->contains('good_conduct')) {
            return back()->withErrors(['verify' => 'Upload your national ID, a selfie holding your ID, and your Certificate of Good Conduct first.']);
        }
        $profile->forceFill(['verification_status' => 'pending', 'verification_note' => null])->save();
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice('Caregiver awaiting verification', $user->name.' submitted their profile for verification.', route('admin.verifications.show', $user)));
        return back()->with('status', 'Submitted. We will check your documents and call your referee, then notify you.');
    }

    /** After uploading a newer Certificate of Good Conduct, ask the team to record its date. */
    public function goodConductRecheck(Request $request)
    {
        $user = $request->user();
        $latest = $user->documents()->where('type', 'good_conduct')->latest('id')->first();
        if (! $latest || $latest->created_at->lt(now()->subDays(30))) {
            return back()->withErrors(['verify' => 'Upload your new Certificate of Good Conduct on the Documents page first.']);
        }
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice('New Good Conduct certificate', $user->name.' uploaded a new Certificate of Good Conduct. Check it and record the issue date.', route('admin.verifications.show', $user)));
        return back()->with('status', 'Thanks. We will check your new certificate and update your profile.');
    }

    public function requests(Request $request)
    {
        $requests = CareRequest::where('caregiver_id', $request->user()->id)->with('family.familyProfile')->latest()->paginate(20);
        return view('caregiver.requests', compact('requests'));
    }

    public function respond(Request $request, CareRequest $careRequest)
    {
        abort_unless($careRequest->caregiver_id === $request->user()->id && $careRequest->status === 'pending', 403);
        $data = $request->validate(['decision' => 'required|in:accepted,declined']);
        $careRequest->update(['status' => $data['decision']]);

        $careRequest->family->notify(new PlatformNotice(
            $data['decision'] === 'accepted' ? 'Caregiver is interested' : 'Caregiver not available',
            $data['decision'] === 'accepted'
                ? $request->user()->publicName().' is interested in your care request. Unlock their contact to call them and arrange an interview.'
                : $request->user()->publicName().' is not available for your request. Try other caregivers.',
            route('caregivers.show', $request->user())
        ));
        return back()->with('status', $data['decision'] === 'accepted' ? 'Accepted. The family has been notified.' : 'Request declined.');
    }

    public function applications(Request $request)
    {
        $applications = $request->user()->applications()->with('job')->latest()->paginate(20);
        return view('caregiver.applications', compact('applications'));
    }

    public function apply(Request $request, CareJob $job)
    {
        $user = $request->user();
        if (! $user->isVerified()) {
            return back()->withErrors(['apply' => 'Your profile must be verified before you can apply.']);
        }
        if (! $user->hasActiveSubscription()) {
            return redirect()->route('payments.index')->withErrors(['apply' => 'You need an active subscription to apply for jobs.']);
        }
        abort_unless($job->status === 'open', 422, 'This job is no longer open.');
        $data = $request->validate(['message' => 'required|string|min:20|max:1000'], ['message.min' => 'Tell the family a little about why you are a good fit (at least 20 characters).']);
        if ($job->applications()->where('caregiver_id', $user->id)->exists()) {
            return back()->withErrors(['apply' => 'You have already applied for this job.']);
        }
        $job->applications()->create(['caregiver_id' => $user->id, 'message' => $data['message']]);
        $job->family->notify(new PlatformNotice('New applicant', $user->publicName().' applied for "'.$job->title.'".', route('family.jobs.show', $job)));
        return redirect()->route('caregiver.applications')->with('status', 'Application sent. The family will contact you if shortlisted.');
    }

    public function withdraw(Request $request, JobApplication $application)
    {
        abort_unless($application->caregiver_id === $request->user()->id && in_array($application->status, ['applied', 'shortlisted']), 403);
        $application->update(['status' => 'withdrawn']);
        return back()->with('status', 'Application withdrawn.');
    }

    /** Families who unlocked this caregiver's contact (so the caregiver knows who may call). */
    public function contacts(Request $request)
    {
        $unlocks = Unlock::where('caregiver_id', $request->user()->id)->with('family.familyProfile')->latest()->paginate(20);
        return view('caregiver.contacts', compact('unlocks'));
    }
}
