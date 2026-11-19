<?php

namespace App\Http\Controllers;

use App\Models\CareJob;
use App\Models\Report;
use App\Models\User;
use App\Notifications\PlatformNotice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request, User $user)
    {
        $reporter = $request->user();
        abort_if($user->id === $reporter->id || $user->isAdmin(), 422);
        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(config('skills.report_reasons')))],
            'details' => 'required|string|min:10|max:2000',
            'care_job_id' => 'nullable|integer|exists:care_jobs,id',
        ]);
        if (! empty($data['care_job_id'])) {
            abort_unless(CareJob::whereKey($data['care_job_id'])->where('family_id', $user->id)->exists(), 422);
        }
        if (Report::where('reporter_id', $reporter->id)->where('reported_id', $user->id)->where('status', 'open')->exists()) {
            return back()->with('status', 'You already reported this account. Our team is looking into it.');
        }
        $report = Report::create($data + ['reporter_id' => $reporter->id, 'reported_id' => $user->id]);

        $paused = false;
        if ($user->isCaregiver()) {
            $profile = $user->caregiverProfile;
            $reporters = Report::where('reported_id', $user->id)->where('status', 'open')->distinct()->count('reporter_id');
            // A family that has actually dealt with this caregiver reporting unsafe behaviour, or several separate reporters, hides the profile until we review.
            $seriousFromClient = $report->reason === 'unsafe' && $reporter->isFamily()
                && ($reporter->hasUnlocked($user) || $reporter->hasWorkedWith($user));
            if (! $profile->paused_at && ($seriousFromClient || $reporters >= config('skills.auto_pause_reporters'))) {
                $profile->forceFill(['paused_at' => now(), 'paused_reason' => 'Reports under review'])->save();
                $user->notify(new PlatformNotice('Profile paused', 'Your profile is hidden from families while we look into a report. We will contact you.', route('dashboard')));
                $paused = true;
            }
        }
        User::where('role', 'admin')->get()->each->notify(new PlatformNotice(
            ($paused ? 'Caregiver auto-paused: ' : 'New report: ').$user->name,
            $reporter->name.' reported '.$user->name.': '.$report->reasonLabel().'.', route('admin.reports')));

        return back()->with('status', 'Thank you. Our team will look into this and may contact you. If anyone is in danger, call 999 or 112 now.');
    }
}
