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
            'featured' => CaregiverProfile::listed()->with('user', 'skills')->withCount(['skills as badges_count' => fn ($q) => $q->earned()])
                ->orderByDesc('rating_avg')->orderByDesc('badges_count')->latest('verified_at')->limit(6)->get(),
            'jobs' => CareJob::open()->latest()->limit(4)->get(),
            'caregiverPlan' => Plan::for('caregiver')->first(),
        ]);
    }

    public function caregivers(Request $request)
    {
        $filter = fn ($q) => $q
            ->when($request->care_type, fn ($q, $v) => $q->whereJsonContains('care_types', $v))
            ->when($request->arrangement, fn ($q, $v) => $q->whereJsonContains('arrangements', $v))
            ->when($request->county, fn ($q, $v) => $q->where('county', $v))
            ->when($request->skill, fn ($q, $v) => $q->whereHas('skills', fn ($s) => $s->earned()->where('skill', $v)));

        $caregivers = $filter(CaregiverProfile::listed()->with('user', 'skills')->withCount(['skills as badges_count' => fn ($q) => $q->earned()]))
            ->orderByDesc('rating_avg')->orderByDesc('badges_count')->orderByDesc('years_experience')->paginate(18)->withQueryString();

        // Nothing matched: show the closest caregivers by relaxing the county first, then everything.
        $suggestions = collect();
        $suggestTitle = null;
        if ($caregivers->isEmpty() && ($request->care_type || $request->arrangement || $request->county || $request->skill)) {
            if ($request->county) {
                $suggestions = CaregiverProfile::listed()->with('user', 'skills')
                    ->when($request->care_type, fn ($q, $v) => $q->whereJsonContains('care_types', $v))
                    ->limit(6)->get();
                $suggestTitle = 'Matching caregivers in other counties';
            }
            if ($suggestions->isEmpty()) {
                $suggestions = CaregiverProfile::listed()->with('user', 'skills')->orderByDesc('rating_avg')->limit(6)->get();
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
            'canReview' => $viewer?->isFamily() && $viewer->hasWorkedWith($caregiver) && ! Review::where('family_id', $viewer->id)->where('caregiver_id', $caregiver->id)->exists(),
            'skills' => $caregiver->skills()->get()->sortByDesc(fn ($s) => [$s->isCertified(), $s->isTested()])->values(),
            'placements' => $profile->placementsCount(),
            'qualifications' => $caregiver->qualifications()->whereIn('status', ['approved', 'pending', 'unverified'])->orderByRaw("status = 'approved' desc")->orderByDesc('year')->get(),
            'alreadyReported' => $viewer && \App\Models\Report::where('reporter_id', $viewer->id)->where('reported_id', $caregiver->id)->where('status', 'open')->exists(),
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
        return view('public.pricing', ['caregiverPlans' => Plan::for('caregiver')->get()]);
    }
}
