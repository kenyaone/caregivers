<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareJob;
use App\Models\CaregiverProfile;
use App\Models\FamilyProfile;
use App\Models\Payment;
use App\Models\Plan;
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
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'note' => 'nullable|required_if:decision,rejected|string|max:255', 'checks' => 'array']);
        $profile = $user->profile();
        abort_unless($profile, 404);

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

        $user->notify($data['decision'] === 'approved'
            ? new PlatformNotice('Account verified', $user->isCaregiver()
                ? 'You are verified. Subscribe to be listed for families and apply for care jobs.'
                : 'Your ID is verified. Caregivers will see the verified badge on your requests.', route('dashboard'))
            : new PlatformNotice('Verification needs attention', 'We could not verify your account: '.$data['note'].'. Update your details and resubmit.', route('dashboard')));

        return redirect()->route('admin.verifications')->with('status', $user->name.' marked '.$data['decision'].'.');
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
