<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\CareRequest;
use App\Models\Review;
use App\Models\Unlock;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FamilyController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        return view('family.dashboard', [
            'profile' => $user->familyProfile,
            'credits' => $user->creditsLeft(),
            'jobs' => $user->careJobs()->withCount(['applications as new_count' => fn ($q) => $q->where('status', 'applied')])->latest()->limit(5)->get(),
            'requests' => CareRequest::where('family_id', $user->id)->with('caregiver.caregiverProfile')->latest()->limit(5)->get(),
            'unlocked' => Unlock::where('family_id', $user->id)->with('caregiver.caregiverProfile')->latest()->limit(6)->get(),
        ]);
    }

    public function editProfile(Request $request)
    {
        return view('family.profile', ['user' => $request->user(), 'profile' => $request->user()->familyProfile]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => ['required', 'regex:/^(\+?254|0)?[17]\d{8}$/'],
            'county' => ['required', Rule::in(config('noc.counties'))],
            'town' => 'required|string|max:80',
            'about' => 'nullable|string|max:1000',
        ]);
        $user->update(['name' => $data['name'], 'phone' => User::normalisePhone($data['phone'])]);
        $user->familyProfile->fill(collect($data)->only(['county', 'town', 'about'])->all())->save();
        return back()->with('status', 'Profile saved.');
    }

    public function submitForVerification(Request $request)
    {
        $user = $request->user();
        if (! $user->familyProfile->isComplete() || ! $user->documents()->where('type', 'national_id')->exists()) {
            return back()->withErrors(['verify' => 'Add your county and town, and upload your national ID first.']);
        }
        $user->familyProfile->forceFill(['verification_status' => 'pending', 'verification_note' => null])->save();
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice('Family awaiting ID check', $user->name.' asked for the ID-verified badge.', route('admin.verifications.show', $user)));
        return back()->with('status', 'Submitted. Verified families get more responses from caregivers.');
    }

    public function unlock(Request $request, User $caregiver)
    {
        $family = $request->user();
        abort_unless($caregiver->isCaregiver() && $caregiver->isVerified(), 404);
        if ($family->hasUnlocked($caregiver)) {
            return back();
        }

        $result = DB::transaction(function () use ($family, $caregiver) {
            $pass = $family->contactPasses()->where('expires_at', '>', now())
                ->whereColumn('credits_used', '<', 'credits_total')->orderBy('expires_at')->lockForUpdate()->first();
            if (! $pass) {
                return false;
            }
            $pass->increment('credits_used');
            return Unlock::create(['family_id' => $family->id, 'caregiver_id' => $caregiver->id, 'contact_pass_id' => $pass->id]);
        });

        if (! $result) {
            return redirect()->route('payments.index')->withErrors(['unlock' => 'You have no contact credits left. Buy a contact pass to unlock caregiver phone numbers.']);
        }
        $caregiver->notify(new PlatformNotice('A family viewed your contact',
            $family->name.' in '.($family->familyProfile->town ?: $family->familyProfile->county ?: 'Kenya').' unlocked your phone number and may call you. Their number: +'.$family->phone.'.',
            route('caregiver.contacts')));
        return back()->with('status', 'Contact unlocked. Call to arrange an interview. Always meet first and check references.');
    }

    public function contacts(Request $request)
    {
        $unlocks = Unlock::where('family_id', $request->user()->id)->with('caregiver.caregiverProfile')->latest()->paginate(20);
        return view('family.contacts', compact('unlocks'));
    }

    public function sendRequest(Request $request, User $caregiver)
    {
        abort_unless($caregiver->isCaregiver() && $caregiver->isVerified(), 404);
        $family = $request->user();
        $data = $request->validate([
            'care_type' => ['required', Rule::in(array_keys(config('noc.care_types')))],
            'arrangement' => ['required', Rule::in(array_keys(config('noc.arrangements')))],
            'start_date' => 'nullable|date|after_or_equal:today',
            'message' => 'required|string|min:20|max:1000',
        ], ['message.min' => 'Describe the care needed (at least 20 characters).']);

        if (CareRequest::where('family_id', $family->id)->where('caregiver_id', $caregiver->id)->where('status', 'pending')->exists()) {
            return back()->withErrors(['request' => 'You already have a pending request with this caregiver.']);
        }
        $care = CareRequest::create($data + ['family_id' => $family->id, 'caregiver_id' => $caregiver->id]);
        $caregiver->notify(new PlatformNotice('New care request',
            $family->name.' ('.($family->familyProfile->town ?: $family->familyProfile->county ?: 'Kenya').') needs '.strtolower(config('noc.care_types')[$care->care_type]).', '.strtolower(config('noc.arrangements')[$care->arrangement]).'. Accept or decline.',
            route('caregiver.requests')));
        return back()->with('status', 'Request sent. We will notify you when the caregiver responds.');
    }

    public function requests(Request $request)
    {
        $requests = CareRequest::where('family_id', $request->user()->id)->with('caregiver.caregiverProfile')->latest()->paginate(20);
        return view('family.requests', compact('requests'));
    }

    public function cancelRequest(Request $request, CareRequest $careRequest)
    {
        abort_unless($careRequest->family_id === $request->user()->id && $careRequest->status === 'pending', 403);
        $careRequest->update(['status' => 'withdrawn']);
        return back()->with('status', 'Request withdrawn.');
    }

    public function review(Request $request, User $caregiver)
    {
        $family = $request->user();
        abort_unless($caregiver->isCaregiver() && $family->hasUnlocked($caregiver), 403);
        $data = $request->validate(['stars' => 'required|integer|between:1,5', 'comment' => 'nullable|string|max:1000']);
        Review::updateOrCreate(['family_id' => $family->id, 'caregiver_id' => $caregiver->id], $data);

        $q = Review::where('caregiver_id', $caregiver->id);
        $caregiver->caregiverProfile->forceFill(['rating_avg' => round($q->avg('stars'), 2), 'rating_count' => $q->count()])->save();
        $caregiver->notify(new PlatformNotice('New review', 'A family rated you '.$data['stars'].'/5.', route('caregivers.show', $caregiver)));
        return back()->with('status', 'Thank you for your review.');
    }
}
