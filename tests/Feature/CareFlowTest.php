<?php

namespace Tests\Feature;

use App\Models\CareJob;
use App\Models\CareRequest;
use App\Models\CaregiverProfile;
use App\Models\Consent;
use App\Models\ContactPass;
use App\Models\FamilyProfile;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Unlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public');
        $this->seed();
    }

    private function caregiver(bool $verified = true, bool $subscribed = true, array $over = []): User
    {
        $u = User::factory()->create(['role' => 'caregiver', 'phone' => '254711111111']);
        $p = CaregiverProfile::create($over + ['user_id' => $u->id, 'headline' => 'Elderly caregiver', 'care_types' => ['elderly', 'bedridden'],
            'arrangements' => ['live_in', 'day'], 'county' => 'Nairobi', 'town' => 'Kileleshwa', 'years_experience' => 5,
            'expected_pay' => 18000, 'pay_unit' => 'per_month', 'bio' => 'Caring and patient.', 'photo_path' => 'photos/x.jpg']);
        if ($verified) {
            $p->forceFill(['verification_status' => 'approved', 'verified_at' => now(), 'good_conduct_issued_on' => now()->subMonth()->toDateString()])->save();
        }
        if ($subscribed) {
            $u->subscriptions()->create(['plan_id' => Plan::where('audience', 'caregiver')->first()->id, 'starts_at' => now(), 'ends_at' => now()->addYear()]);
        }
        Consent::grantAll($u);
        return $u;
    }

    private function family(int $credits = 0): User
    {
        $u = User::factory()->create(['role' => 'family', 'phone' => '254722222222']);
        FamilyProfile::create(['user_id' => $u->id, 'county' => 'Nairobi', 'town' => 'Kileleshwa']);
        if ($credits) {
            ContactPass::create(['user_id' => $u->id, 'plan_id' => Plan::where('audience', 'family')->first()->id, 'credits_total' => $credits, 'expires_at' => now()->addDays(30)]);
        }
        Consent::grantAll($u);
        return $u;
    }

    public function test_public_pages_load(): void
    {
        $this->caregiver();
        foreach (['/', '/caregivers', '/jobs', '/pricing', '/terms', '/login', '/register', '/register?as=caregiver'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/caregivers')->assertSee('Elderly caregiver');
    }

    public function test_only_verified_subscribed_available_caregivers_are_listed(): void
    {
        $listed = $this->caregiver();
        $unverified = $this->caregiver(false, true, ['headline' => 'Unverified one']);
        $unsubscribed = $this->caregiver(true, false, ['headline' => 'Unsubscribed one']);
        $res = $this->get('/caregivers');
        $res->assertSee('Elderly caregiver')->assertDontSee('Unverified one')->assertDontSee('Unsubscribed one');
        $this->get("/caregivers/{$unverified->id}")->assertNotFound();
        $this->get("/caregivers/{$listed->id}")->assertOk()->assertDontSee('254711111111');
        $this->get('/caregivers?care_type=elderly&county=Nairobi')->assertSee('Elderly caregiver');
        $this->get('/caregivers?care_type=children')->assertSee('Other verified caregivers');
    }

    public function test_caregiver_registration_and_verification_submission(): void
    {
        $this->post('/register', ['role' => 'caregiver', 'name' => 'Grace Njeri', 'email' => 'grace@example.com', 'phone' => '0711000000',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'consent' => ['terms' => '1', 'data' => '1', 'caregiver_declaration' => '1', 'caregiver_independent' => '1']])->assertRedirect(route('caregiver.profile'));
        $u = User::where('email', 'grace@example.com')->first();
        $this->assertTrue($u->isCaregiver());
        $this->assertSame(4, $u->consents()->count());

        $this->put('/caregiver/profile', ['name' => 'Grace Njeri', 'phone' => '0711000000', 'photo' => UploadedFile::fake()->image('me.jpg'),
            'headline' => 'Nanny and elderly carer', 'care_types' => ['children', 'elderly'], 'arrangements' => ['day'], 'county' => 'Nakuru',
            'pay_unit' => 'per_month', 'expected_pay' => 15000, 'bio' => 'Five years caring for children and elderly people.', 'available' => '1'])->assertSessionHasNoErrors();

        $this->post('/caregiver/verify')->assertSessionHasErrors('verify'); // no documents yet
        foreach (['national_id', 'good_conduct'] as $t) {
            $this->post('/my/documents', ['type' => $t, 'file' => UploadedFile::fake()->create('d.pdf', 50, 'application/pdf')])->assertSessionHasNoErrors();
        }
        $this->post('/caregiver/verify')->assertSessionHasErrors('verify'); // selfie still missing
        $this->post('/my/documents', ['type' => 'selfie', 'file' => UploadedFile::fake()->create('s.pdf', 50, 'application/pdf')])->assertSessionHasErrors('file'); // must be a photo
        $this->post('/my/documents', ['type' => 'selfie', 'file' => UploadedFile::fake()->image('selfie.jpg')])->assertSessionHasNoErrors();
        $this->post('/caregiver/verify')->assertSessionHasNoErrors();
        $this->assertSame('pending', $u->caregiverProfile->fresh()->verification_status);
    }

    public function test_family_unlock_uses_credit_and_reveals_contact(): void
    {
        $cg = $this->caregiver();
        $doc = $cg->documents()->create(['type' => 'good_conduct', 'path' => 'documents/gc.pdf', 'original_name' => 'gc.pdf']);
        $id = $cg->documents()->create(['type' => 'national_id', 'path' => 'documents/id.pdf', 'original_name' => 'id.pdf']);
        Storage::disk('local')->put('documents/gc.pdf', 'x');
        Storage::disk('local')->put('documents/id.pdf', 'x');

        $noCredit = $this->family(0);
        $this->actingAs($noCredit)->post("/family/caregivers/{$cg->id}/unlock")->assertRedirect(route('payments.index'));
        $this->actingAs($noCredit)->get("/documents/{$doc->id}")->assertForbidden();

        $fam = $this->family(3);
        $this->actingAs($fam)->get("/caregivers/{$cg->id}")->assertDontSee('254711111111');
        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/unlock")->assertSessionHasNoErrors();
        $this->assertSame(2, $fam->creditsLeft());
        $this->actingAs($fam)->get("/caregivers/{$cg->id}")->assertSee('254711111111');
        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/unlock");
        $this->assertSame(2, $fam->creditsLeft(), 'unlocking twice must not use a second credit');
        $this->actingAs($fam)->get("/documents/{$doc->id}")->assertOk();
        $this->actingAs($fam)->get("/documents/{$id->id}")->assertForbidden(); // ID card stays private

        // Unlocking alone is not enough to review: the caregiver must have accepted a request or been hired
        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/review", ['stars' => 5])->assertForbidden();
        CareRequest::create(['family_id' => $fam->id, 'caregiver_id' => $cg->id, 'care_type' => 'elderly', 'arrangement' => 'day', 'message' => 'Help', 'status' => 'accepted']);
        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/review", ['stars' => 5, 'comment' => 'Wonderful'])->assertSessionHasNoErrors();
        $this->assertEquals(5, $cg->caregiverProfile->fresh()->rating_avg);
        $this->actingAs($noCredit)->post("/family/caregivers/{$cg->id}/review", ['stars' => 1])->assertForbidden();
    }

    public function test_care_request_flow(): void
    {
        $cg = $this->caregiver();
        $fam = $this->family();
        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/request", ['care_type' => 'elderly', 'arrangement' => 'live_in',
            'message' => 'My mother is 80 and needs help with bathing and meals.'])->assertSessionHasNoErrors();
        $req = CareRequest::first();
        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/request", ['care_type' => 'elderly', 'arrangement' => 'live_in',
            'message' => 'Second request with enough characters here.'])->assertSessionHasErrors('request');

        $other = $this->caregiver();
        $this->actingAs($other)->post("/caregiver/requests/{$req->id}", ['decision' => 'accepted'])->assertForbidden();
        $this->actingAs($cg)->post("/caregiver/requests/{$req->id}", ['decision' => 'accepted'])->assertSessionHasNoErrors();
        $this->assertSame('accepted', $req->fresh()->status);
    }

    public function test_job_posting_application_and_hiring(): void
    {
        $fam = $this->family();
        $cg = $this->caregiver();
        $job = ['title' => 'Live-in carer for my father', 'care_type' => 'elderly', 'care_recipient' => 'Father, 82, wheelchair user', 'arrangement' => 'live_in',
            'county' => 'Nairobi', 'town' => 'Kileleshwa', 'pay_amount' => 20000, 'pay_unit' => 'per_month', 'description' => 'Bathing, meals, medication reminders.'];
        $this->actingAs($fam)->post('/family/jobs', $job)->assertSessionHasErrors('confirm');
        $this->actingAs($fam)->post('/family/jobs', $job + ['confirm' => '1'])->assertSessionHasNoErrors();
        $careJob = CareJob::first();
        Notification::assertSentTo($cg, \App\Notifications\PlatformNotice::class); // matching caregiver alerted

        $unsub = $this->caregiver(true, false);
        $this->actingAs($unsub)->post("/caregiver/jobs/{$careJob->id}/apply", ['message' => 'I have cared for elderly people for years.'])->assertRedirect(route('payments.index'));

        $this->actingAs($cg)->post("/caregiver/jobs/{$careJob->id}/apply", ['message' => 'I have cared for elderly people for five years.'])->assertRedirect(route('caregiver.applications'));
        $app = $careJob->applications()->first();
        $this->actingAs($this->family())->post("/family/applications/{$app->id}", ['status' => 'hired'])->assertForbidden();
        $this->actingAs($fam)->post("/family/applications/{$app->id}", ['status' => 'hired'])->assertSessionHasNoErrors();
        $this->assertSame('filled', $careJob->fresh()->status);

        foreach (['/family', '/family/jobs', "/family/jobs/{$careJob->id}", "/family/jobs/{$careJob->id}/edit", '/family/contacts', '/family/requests', '/family/profile', '/my/payments', '/my/documents'] as $url) {
            $this->actingAs($fam)->get($url)->assertOk();
        }
        foreach (['/caregiver', '/caregiver/profile', '/caregiver/requests', '/caregiver/applications', '/caregiver/contacts', '/my/payments', '/my/documents', "/jobs/{$careJob->id}"] as $url) {
            $this->actingAs($cg)->get($url)->assertOk();
        }
    }

    public function test_payments_grant_subscription_or_contact_pass(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fam = $this->family();
        $famPlan = Plan::where('audience', 'family')->where('name', 'Contact Pass')->first();
        $this->actingAs($fam)->post('/my/payments', ['plan_id' => $famPlan->id, 'reference' => 'ABC123DEF4', 'phone' => '0722222222'])->assertSessionHasNoErrors();
        $cgPlan = Plan::where('audience', 'caregiver')->first();
        $this->actingAs($fam)->post('/my/payments', ['plan_id' => $cgPlan->id, 'reference' => 'ZZZ123DEF4', 'phone' => '0722222222'])->assertNotFound(); // wrong audience

        $this->actingAs($admin)->post('/admin/payments/'.Payment::first()->id, ['decision' => 'confirm'])->assertSessionHasNoErrors();
        $this->assertSame(3, $fam->creditsLeft());

        $cg = $this->caregiver(true, false);
        $this->actingAs($cg)->post('/my/payments', ['plan_id' => $cgPlan->id, 'reference' => 'QWE123RTY0', 'phone' => '0711111111'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/payments/'.Payment::where('reference', 'QWE123RTY0')->first()->id, ['decision' => 'confirm']);
        $this->assertTrue($cg->fresh()->hasActiveSubscription());
    }

    public function test_admin_verification_requires_checklist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cg = $this->caregiver(false);
        $cg->caregiverProfile->forceFill(['verification_status' => 'pending'])->save();
        foreach (['/admin', '/admin/verifications', "/admin/verifications/{$cg->id}", '/admin/payments', '/admin/plans', '/admin/users', '/admin/jobs'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $this->actingAs($admin)->post("/admin/verifications/{$cg->id}", ['decision' => 'approved', 'good_conduct_issued_on' => now()->subMonth()->toDateString()])->assertSessionHasErrors('checks');
        $checks = array_fill_keys(array_keys(config('noc.verification_checks.caregiver')), '1');
        $this->actingAs($admin)->post("/admin/verifications/{$cg->id}", ['decision' => 'approved', 'checks' => $checks])->assertSessionHasErrors('good_conduct_issued_on');
        $this->actingAs($admin)->post("/admin/verifications/{$cg->id}", ['decision' => 'approved', 'checks' => $checks, 'good_conduct_issued_on' => now()->subMonths(7)->toDateString()])->assertSessionHasErrors('good_conduct_issued_on');
        $this->actingAs($admin)->post("/admin/verifications/{$cg->id}", ['decision' => 'approved', 'checks' => $checks, 'good_conduct_issued_on' => now()->subMonth()->toDateString()])->assertRedirect();
        $this->assertTrue($cg->fresh()->isVerified());
        $this->actingAs($admin)->post("/admin/verifications/{$cg->id}", ['decision' => 'rejected', 'note' => 'Blurry ID', 'good_conduct_issued_on' => ''])->assertSessionHasNoErrors();
    }

    public function test_access_control(): void
    {
        $cg = $this->caregiver();
        $fam = $this->family();
        $this->get('/family')->assertRedirect('/login');
        $this->actingAs($cg)->get('/family')->assertForbidden();
        $this->actingAs($fam)->get('/caregiver')->assertForbidden();
        $this->actingAs($fam)->get('/admin')->assertForbidden();
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        $fresh = User::factory()->create(['role' => 'family']);
        FamilyProfile::create(['user_id' => $fresh->id]);
        $this->actingAs($fresh)->get('/family')->assertRedirect(route('consent.show'));
    }
}
