<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareJob;
use App\Models\CaregiverProfile;
use App\Models\CaregiverSkill;
use App\Models\FamilyProfile;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Qualification;
use App\Models\Report;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\Unlock;
use App\Models\User;
use App\Notifications\PlatformNotice;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'stats' => [
                'Caregivers' => User::where('role', 'caregiver')->count(),
                'Verified caregivers' => CaregiverProfile::where('verification_status', 'approved')->count(),
                'Listed now' => CaregiverProfile::listed()->count(),
                'Families' => User::where('role', 'family')->count(),
                'Open jobs' => CareJob::open()->count(),
                'Contacts unlocked' => Unlock::count(),
                'Active caregiver subscriptions' => Subscription::where('ends_at', '>', now())->distinct('user_id')->count('user_id'),
                'Revenue this month (KES)' => number_format(Payment::where('status', 'paid')->where('paid_at', '>=', now()->startOfMonth())->sum('amount')),
            ],
            'pendingVerifications' => CaregiverProfile::where('verification_status', 'pending')->count() + FamilyProfile::where('verification_status', 'pending')->count(),
            'pendingPayments' => Payment::where('status', 'pending')->count(),
            'openReports' => Report::where('status', 'open')->count(),
            'pendingCerts' => CaregiverSkill::where('cert_status', 'pending')->count() + Qualification::where('status', 'pending')->count(),
        ]);
    }

    public function verifications(Request $request)
    {
        $status = $request->query('status', 'pending');
        return view('admin.verifications', [
            'status' => $status,
            'caregivers' => CaregiverProfile::with('user')->where('verification_status', $status)->latest('updated_at')->get(),
            'families' => FamilyProfile::with('user')->where('verification_status', $status)->latest('updated_at')->get(),
        ]);
    }

    public static function checklistFor(User $user): array
    {
        return config('noc.verification_checks.'.($user->isCaregiver() ? 'caregiver' : 'family'));
    }

    public function showVerification(User $user)
    {
        abort_if($user->isAdmin(), 404);
        return view('admin.verification-show', [
            'subject' => $user->load('documents'),
            'profile' => $user->profile(),
            'checklist' => self::checklistFor($user),
            'consents' => $user->consents()->latest('id')->limit(20)->get(),
            'reviews' => Review::where('caregiver_id', $user->id)->latest()->limit(10)->get(),
        ]);
    }

    public function decide(Request $request, User $user)
    {
        $profile = $user->profile();
        abort_unless($profile, 404);
        $gcRule = $user->isCaregiver() ? self::goodConductDateRule() : ['nullable'];
        if ($user->isCaregiver()) {
            array_unshift($gcRule, 'nullable', 'required_if:decision,approved');
        }
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'note' => 'nullable|required_if:decision,rejected|string|max:255',
            'checks' => 'array',
            'good_conduct_issued_on' => $gcRule,
        ], ['good_conduct_issued_on.after' => 'The Good Conduct certificate is older than '.config('skills.good_conduct_valid_months').' months. Ask for a new one.']);

        $checklist = self::checklistFor($user);
        $ticked = array_keys(array_filter($request->input('checks', [])));
        if ($data['decision'] === 'approved' && array_diff(array_keys($checklist), $ticked)) {
            return back()->withInput()->withErrors(['checks' => 'Tick every verification check before approving.']);
        }

        $profile->forceFill([
            'verification_status' => $data['decision'],
            'verification_note' => $data['note'] ?? null,
            'verified_at' => $data['decision'] === 'approved' ? now() : null,
            'verified_by' => $request->user()->id,
            'verification_checks' => ['ticked' => array_values(array_intersect($ticked, array_keys($checklist))), 'by' => $request->user()->email, 'at' => now()->toDateTimeString()],
        ])->save();
        if ($user->isCaregiver() && ! empty($data['good_conduct_issued_on'])) {
            $profile->forceFill(['good_conduct_issued_on' => $data['good_conduct_issued_on'], 'good_conduct_warned_at' => null])->save();
        }

        $user->notify($data['decision'] === 'approved'
            ? new PlatformNotice('Account verified', $user->isCaregiver()
                ? 'You are verified. Subscribe to be listed for families and apply for care jobs.'
                : 'Your ID is verified. Caregivers will see the verified badge on your requests.', route('dashboard'))
            : new PlatformNotice('Verification needs attention', 'We could not verify your account: '.$data['note'].'. Update your details and resubmit.', route('dashboard')));

        return redirect()->route('admin.verifications')->with('status', $user->name.' marked '.$data['decision'].'.');
    }

    public static function goodConductDateRule(): array
    {
        return ['date', 'before_or_equal:today', 'after:'.now()->subMonths(config('skills.good_conduct_valid_months'))->toDateString()];
    }

    /** Record the issue date of a newer Certificate of Good Conduct without a full re-verification. */
    public function updateGoodConduct(Request $request, User $user)
    {
        abort_unless($user->isCaregiver(), 404);
        $data = $request->validate(['good_conduct_issued_on' => array_merge(['required'], self::goodConductDateRule())],
            ['good_conduct_issued_on.after' => 'That certificate is already older than '.config('skills.good_conduct_valid_months').' months.']);
        $user->caregiverProfile->forceFill(['good_conduct_issued_on' => $data['good_conduct_issued_on'], 'good_conduct_warned_at' => null])->save();
        $until = $user->caregiverProfile->fresh()->goodConductValidUntil();
        $user->notify(new PlatformNotice('Good Conduct updated', 'Your new Certificate of Good Conduct is recorded. It counts until '.$until->format('j M Y').'.', route('dashboard')));
        return back()->with('status', 'Good Conduct date saved. Valid until '.$until->format('j M Y').'.');
    }

    public function reports(Request $request)
    {
        $status = $request->query('status', 'open');
        return view('admin.reports', [
            'status' => $status,
            'reports' => Report::with('reporter', 'reported.caregiverProfile', 'job')->where('status', $status)->latest()->paginate(30)->withQueryString(),
        ]);
    }

    public function decideReport(Request $request, Report $report)
    {
        $data = $request->validate(['status' => 'required|in:upheld,dismissed', 'admin_note' => 'nullable|string|max:255']);
        $report->forceFill($data + ['resolved_by' => $request->user()->id, 'resolved_at' => now()])->save();
        $profile = $report->reported->isCaregiver() ? $report->reported->caregiverProfile : null;
        // Un-pause automatically once nothing is left open against a caregiver and the admin dismissed the report.
        if ($profile?->paused_at && $data['status'] === 'dismissed' && ! Report::where('reported_id', $report->reported_id)->where('status', 'open')->exists()) {
            $profile->forceFill(['paused_at' => null, 'paused_reason' => null])->save();
            $report->reported->notify(new PlatformNotice('Profile visible again', 'We looked into the report and your profile is listed again.', route('dashboard')));
        }
        return back()->with('status', 'Report marked '.$data['status'].'.');
    }

    public function togglePause(Request $request, User $user)
    {
        abort_unless($user->isCaregiver(), 404);
        $profile = $user->caregiverProfile;
        $paused = ! $profile->paused_at;
        $profile->forceFill(['paused_at' => $paused ? now() : null, 'paused_reason' => $paused ? 'Paused by admin' : null])->save();
        $user->notify(new PlatformNotice($paused ? 'Profile paused' : 'Profile visible again',
            $paused ? 'Your profile is hidden from families while we look into a concern. We will contact you.' : 'Your profile is listed for families again.', route('dashboard')));
        return back()->with('status', $user->name.($paused ? ' paused (hidden from families).' : ' listed again.'));
    }

    public function skillCerts(Request $request)
    {
        return view('admin.skills', [
            'items' => CaregiverSkill::with('user', 'certificate')->where('cert_status', $request->query('status', 'pending'))->latest('updated_at')->paginate(30)->withQueryString(),
            'status' => $request->query('status', 'pending'),
        ]);
    }

    public function decideSkillCert(Request $request, CaregiverSkill $caregiverSkill)
    {
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'cert_note' => 'nullable|required_if:decision,rejected|string|max:255']);
        $ok = $data['decision'] === 'approved';
        $caregiverSkill->forceFill([
            'cert_status' => $data['decision'],
            'cert_note' => $data['cert_note'] ?? null,
            'certified_at' => $ok ? now() : null,
            'certified_by' => $ok ? $request->user()->id : null,
            'declared' => true,
        ])->save();
        $caregiverSkill->user->notify(new PlatformNotice($ok ? 'Skill badge earned' : 'Certificate not accepted',
            $ok ? 'Your '.$caregiverSkill->label().' certificate was checked. The "Certificate checked" badge now shows on your profile.'
                : 'We could not accept your '.$caregiverSkill->label().' certificate: '.$data['cert_note'], route('caregiver.skills')));
        return back()->with('status', 'Certificate '.$data['decision'].'.');
    }

    public function qualifications(Request $request)
    {
        $status = $request->query('status', 'pending');
        return view('admin.qualifications', [
            'status' => $status,
            'items' => Qualification::with('user', 'document')->where('status', $status)->latest('updated_at')->paginate(30)->withQueryString(),
        ]);
    }

    public function decideQualification(Request $request, Qualification $qualification)
    {
        abort_unless($qualification->document_id, 422);
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'note' => 'nullable|required_if:decision,rejected|string|max:255']);
        $ok = $data['decision'] === 'approved';
        $qualification->forceFill(['status' => $data['decision'], 'note' => $data['note'] ?? null,
            'checked_by' => $request->user()->id, 'checked_at' => now()])->save();
        $qualification->user->notify(new PlatformNotice($ok ? 'Certificate checked' : 'Certificate not accepted',
            $ok ? 'Your '.$qualification->course.' certificate from '.$qualification->institution.' now shows as checked on your profile.'
                : 'We could not accept your '.$qualification->course.' certificate: '.$data['note'], route('caregiver.qualifications')));
        return back()->with('status', 'Qualification '.$data['decision'].'.');
    }

    public function payments(Request $request)
    {
        return view('admin.payments', [
            'payments' => Payment::with('user', 'plan')->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))->latest()->paginate(30)->withQueryString(),
        ]);
    }

    public function confirmPayment(Request $request, Payment $payment, SubscriptionService $subs)
    {
        abort_unless($payment->status === 'pending', 422);
        if ($request->input('decision') === 'reject') {
            $payment->update(['status' => 'rejected']);
            $payment->user->notify(new PlatformNotice('Payment not confirmed', 'We could not confirm M-Pesa code '.$payment->reference.'. Check the code or contact support.', route('payments.index')));
            return back()->with('status', 'Payment rejected.');
        }
        $subs->markPaid($payment);
        return back()->with('status', 'Payment confirmed and activated.');
    }

    public function plans()
    {
        return view('admin.plans', ['plans' => Plan::orderBy('audience')->orderBy('price')->get()]);
    }

    public function savePlan(Request $request, ?Plan $plan = null)
    {
        $data = $request->validate([
            'audience' => 'required|in:caregiver,family',
            'name' => 'required|string|max:60',
            'price' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1|max:730',
            'credits' => 'nullable|required_if:audience,family|integer|min:1|max:500',
            'description' => 'nullable|string|max:255',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if ($data['audience'] === 'caregiver') {
            $data['credits'] = null;
        }
        $plan && $plan->exists ? $plan->update($data) : Plan::create($data);
        return back()->with('status', 'Plan saved.');
    }

    public function users(Request $request)
    {
        return view('admin.users', [
            'users' => User::with('caregiverProfile', 'familyProfile')
                ->when($request->query('role'), fn ($q, $r) => $q->where('role', $r))
                ->when($request->query('q'), fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")))
                ->latest()->paginate(30)->withQueryString(),
        ]);
    }

    public function toggleUser(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422);
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('status', $user->name.($user->is_active ? ' reactivated.' : ' suspended.'));
    }

    public function jobs(Request $request)
    {
        return view('admin.jobs', [
            'jobs' => CareJob::with('family')->withCount('applications')->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))->latest()->paginate(30)->withQueryString(),
        ]);
    }
}
