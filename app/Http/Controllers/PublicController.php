<?php

namespace App\Http\Controllers;

use App\Models\CareJob;
use App\Models\CaregiverProfile;
use App\Models\Plan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'featured' => CaregiverProfile::listed()->with('user')->orderByDesc('rating_avg')->latest('verified_at')->limit(6)->get(),
            'jobs' => CareJob::open()->latest()->limit(4)->get(),
            'caregiverPlan' => Plan::for('caregiver')->first(),
            'familyPlan' => Plan::for('family')->first(),
        ]);
    }

    public function caregivers(Request $request)
    {
        $filter = fn ($q) => $q
            ->when($request->care_type, fn ($q, $v) => $q->whereJsonContains('care_types', $v))
            ->when($request->arrangement, fn ($q, $v) => $q->whereJsonContains('arrangements', $v))
            ->when($request->county, fn ($q, $v) => $q->where('county', $v));

        $caregivers = $filter(CaregiverProfile::listed()->with('user'))
            ->orderByDesc('rating_avg')->orderByDesc('years_experience')->paginate(18)->withQueryString();

        // Nothing matched: show the closest caregivers by relaxing the county first, then everything.
        $suggestions = collect();
        $suggestTitle = null;
        if ($caregivers->isEmpty() && ($request->care_type || $request->arrangement || $request->county)) {
            if ($request->county) {
                $suggestions = CaregiverProfile::listed()->with('user')
                    ->when($request->care_type, fn ($q, $v) => $q->whereJsonContains('care_types', $v))
                    ->limit(6)->get();
                $suggestTitle = 'Matching caregivers in other counties';
            }
            if ($suggestions->isEmpty()) {
                $suggestions = CaregiverProfile::listed()->with('user')->orderByDesc('rating_avg')->limit(6)->get();
                $suggestTitle = 'Other verified caregivers';
            }
        }

        return view('public.caregivers', compact('caregivers', 'suggestions', 'suggestTitle'));
    }

    public function caregiver(Request $request, User $caregiver)
    {
        $profile = $caregiver->caregiverProfile;
        abort_unless($caregiver->isCaregiver() && $profile, 404);
        $viewer = $request->user();
        $isOwnerOrAdmin = $viewer && ($viewer->id === $caregiver->id || $viewer->isAdmin());
        abort_unless($isOwnerOrAdmin || CaregiverProfile::listed()->whereKey($profile->id)->exists(), 404);

        $unlocked = $viewer?->isFamily() && $viewer->hasUnlocked($caregiver);
        return view('public.caregiver', [
            'caregiver' => $caregiver,
            'profile' => $profile,
            'reviews' => Review::where('caregiver_id', $caregiver->id)->with('family')->latest()->limit(10)->get(),
            'unlocked' => $unlocked || $isOwnerOrAdmin,
            'canReview' => $unlocked && ! Review::where('family_id', $viewer->id)->where('caregiver_id', $caregiver->id)->exists(),
            'credits' => $viewer?->isFamily() ? $viewer->creditsLeft() : 0,
        ]);
    }

    public function jobs(Request $request)
    {
        $jobs = CareJob::open()
            ->when($request->care_type, fn ($q, $v) => $q->where('care_type', $v))
            ->when($request->county, fn ($q, $v) => $q->where('county', $v))
            ->when($request->arrangement, fn ($q, $v) => $q->where('arrangement', $v))
            ->latest()->paginate(15)->withQueryString();
        return view('public.jobs', compact('jobs'));
    }

    public function job(Request $request, CareJob $job)
    {
        $viewer = $request->user();
        $application = $viewer?->isCaregiver() ? $job->applications()->where('caregiver_id', $viewer->id)->first() : null;
        abort_unless($job->status === 'open' || $application || ($viewer && ($viewer->id === $job->family_id || $viewer->isAdmin())), 404);
        return view('public.job', compact('job', 'application'));
    }

    public function pricing()
    {
        return view('public.pricing', ['caregiverPlans' => Plan::for('caregiver')->get(), 'familyPlans' => Plan::for('family')->get()]);
    }
}