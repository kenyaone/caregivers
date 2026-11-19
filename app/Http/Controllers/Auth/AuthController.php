<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CaregiverProfile;
use App\Models\Consent;
use App\Models\FamilyProfile;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those details do not match our records.'])->onlyInput('email');
        }
        if (! Auth::user()->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function showRegister(Request $request)
    {
        return view('auth.register', ['role' => $request->query('as') === 'caregiver' ? 'caregiver' : 'family']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:caregiver,family',
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => ['required', 'regex:/^(\+?254|0)?[17]\d{8}$/'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
            'consent' => 'required|array',
            'consent.terms' => 'accepted',
            'consent.data' => 'accepted',
            'consent.caregiver_declaration' => 'exclude_unless:role,caregiver|accepted',
            'consent.caregiver_independent' => 'exclude_unless:role,caregiver|accepted',
            'consent.family_declaration' => 'exclude_unless:role,family|accepted',
        ], ['phone.regex' => 'Enter a valid Kenyan phone number, e.g. 0712345678.', 'consent.*.accepted' => 'Please tick every statement to create your account.', 'consent.required' => 'Please tick every statement to create your account.']);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => User::normalisePhone($data['phone']),
            'password' => $data['password'],
            'role' => $data['role'],
        ]);
        $user->isCaregiver()
            ? CaregiverProfile::create(['user_id' => $user->id])
            : FamilyProfile::create(['user_id' => $user->id]);

        foreach (Consent::requiredFor($user) as $type) {
            Consent::record($user, $type, $request);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $user->isCaregiver()
            ? redirect()->route('caregiver.profile')->with('status', 'Welcome! Complete your profile and upload your ID and Certificate of Good Conduct so we can verify you.')
            : redirect()->route('caregivers.index')->with('status', 'Welcome! Browse verified caregivers, or post a care job.');
    }

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email'));
        return back()->with('status', 'If that email is registered, a reset link is on its way.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', Rules\Password::min(8)]]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password reset. You can now log in.')
            : back()->withErrors(['email' => __($status)]);
    }
}
