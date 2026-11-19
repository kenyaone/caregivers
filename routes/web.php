<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Caregiver\CaregiverController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Family\FamilyController;
use App\Http\Controllers\Family\JobController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/caregivers', [PublicController::class, 'caregivers'])->name('caregivers.index');
Route::get('/caregivers/{caregiver}', [PublicController::class, 'caregiver'])->name('caregivers.show');
Route::get('/jobs', [PublicController::class, 'jobs'])->name('jobs.index');
Route::get('/jobs/{job}', [PublicController::class, 'job'])->name('jobs.show');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::view('/terms', 'public.terms')->name('terms');
Route::post('/webhooks/kopokopo', [WebhookController::class, 'kopokopo'])->name('webhooks.kopokopo');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/agree', [ConsentController::class, 'show'])->name('consent.show');
    Route::post('/agree', [ConsentController::class, 'store'])->name('consent.store');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    // Phone/app alerts (web push)
    Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->middleware('throttle:20,1')->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    Route::get('/documents/{document}', [DocumentController::class, 'download'])->name('documents.download');

    Route::middleware(['role:caregiver,family', 'consented'])->group(function () {
        Route::get('/my/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/my/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('/my/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::get('/my/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/my/payments', [PaymentController::class, 'store'])->middleware('throttle:5,1')->name('payments.store');
        Route::post('/report/{user}', [\App\Http\Controllers\ReportController::class, 'store'])->middleware('throttle:5,1')->name('reports.store');
    });

    Route::middleware(['role:caregiver', 'consented'])->prefix('caregiver')->name('caregiver.')->group(function () {
        Route::get('/', [CaregiverController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [CaregiverController::class, 'editProfile'])->name('profile');
        Route::put('/profile', [CaregiverController::class, 'updateProfile']);
        Route::post('/verify', [CaregiverController::class, 'submitForVerification'])->name('verify');
        Route::get('/requests', [CaregiverController::class, 'requests'])->name('requests');
        Route::post('/requests/{careRequest}', [CaregiverController::class, 'respond'])->name('requests.respond');
        Route::get('/applications', [CaregiverController::class, 'applications'])->name('applications');
        Route::post('/jobs/{job}/apply', [CaregiverController::class, 'apply'])->name('apply');
        Route::post('/applications/{application}/withdraw', [CaregiverController::class, 'withdraw'])->name('applications.withdraw');
        Route::get('/contacts', [CaregiverController::class, 'contacts'])->name('contacts');
        Route::post('/good-conduct', [CaregiverController::class, 'goodConductRecheck'])->middleware('throttle:3,1')->name('goodconduct');
        Route::get('/qualifications', [\App\Http\Controllers\Caregiver\QualificationController::class, 'index'])->name('qualifications');
        Route::post('/qualifications', [\App\Http\Controllers\Caregiver\QualificationController::class, 'store'])->middleware('throttle:10,1')->name('qualifications.store');
        Route::post('/qualifications/{qualification}/certificate', [\App\Http\Controllers\Caregiver\QualificationController::class, 'certificate'])->middleware('throttle:10,1')->name('qualifications.certificate');
        Route::delete('/qualifications/{qualification}', [\App\Http\Controllers\Caregiver\QualificationController::class, 'destroy'])->name('qualifications.destroy');
        Route::get('/skills', [\App\Http\Controllers\Caregiver\SkillController::class, 'index'])->name('skills');
        Route::post('/skills', [\App\Http\Controllers\Caregiver\SkillController::class, 'declare'])->name('skills.declare');
        Route::post('/skills/certificate', [\App\Http\Controllers\Caregiver\SkillController::class, 'certificate'])->middleware('throttle:10,1')->name('skills.certificate');
        Route::get('/skills/{skill}/quiz', [\App\Http\Controllers\Caregiver\SkillController::class, 'quiz'])->name('skills.quiz');
        Route::post('/skills/{skill}/quiz', [\App\Http\Controllers\Caregiver\SkillController::class, 'submit'])->middleware('throttle:10,1')->name('skills.submit');
    });

    Route::middleware(['role:family', 'consented'])->prefix('family')->name('family.')->group(function () {
        Route::get('/', [FamilyController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [FamilyController::class, 'editProfile'])->name('profile');
        Route::put('/profile', [FamilyController::class, 'updateProfile']);
        Route::post('/verify', [FamilyController::class, 'submitForVerification'])->name('verify');
        Route::post('/caregivers/{caregiver}/unlock', [FamilyController::class, 'unlock'])->middleware('throttle:20,1')->name('unlock');
        Route::post('/caregivers/{caregiver}/request', [FamilyController::class, 'sendRequest'])->middleware('throttle:10,1')->name('request');
        Route::post('/caregivers/{caregiver}/review', [FamilyController::class, 'review'])->name('review');
        Route::get('/contacts', [FamilyController::class, 'contacts'])->name('contacts');
        Route::get('/requests', [FamilyController::class, 'requests'])->name('requests');
        Route::post('/requests/{careRequest}/cancel', [FamilyController::class, 'cancelRequest'])->name('requests.cancel');
        Route::post('/jobs/{job}/close', [JobController::class, 'close'])->name('jobs.close');
        Route::post('/applications/{application}', [JobController::class, 'decide'])->name('applications.decide');
        Route::resource('jobs', JobController::class)->except('destroy');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/verifications', [AdminController::class, 'verifications'])->name('verifications');
        Route::get('/verifications/{user}', [AdminController::class, 'showVerification'])->name('verifications.show');
        Route::post('/verifications/{user}', [AdminController::class, 'decide'])->name('verifications.decide');
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
        Route::post('/payments/{payment}', [AdminController::class, 'confirmPayment'])->name('payments.confirm');
        Route::get('/plans', [AdminController::class, 'plans'])->name('plans');
        Route::post('/plans/{plan?}', [AdminController::class, 'savePlan'])->name('plans.save');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
        Route::get('/jobs', [AdminController::class, 'jobs'])->name('jobs');
        Route::post('/verifications/{user}/good-conduct', [AdminController::class, 'updateGoodConduct'])->name('verifications.goodconduct');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::post('/reports/{report}', [AdminController::class, 'decideReport'])->name('reports.decide');
        Route::post('/caregivers/{user}/pause', [AdminController::class, 'togglePause'])->name('caregivers.pause');
        Route::get('/skills', [AdminController::class, 'skillCerts'])->name('skills');
        Route::get('/qualifications', [AdminController::class, 'qualifications'])->name('qualifications');
        Route::post('/qualifications/{qualification}', [AdminController::class, 'decideQualification'])->name('qualifications.decide');
        Route::post('/skills/{caregiverSkill}', [AdminController::class, 'decideSkillCert'])->name('skills.decide');
    });
});
