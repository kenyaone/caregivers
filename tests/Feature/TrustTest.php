<?php

namespace Tests\Feature;

use App\Models\CareRequest;
use App\Models\CaregiverProfile;
use App\Models\CaregiverSkill;
use App\Models\Consent;
use App\Models\ContactPass;
use App\Models\FamilyProfile;
use App\Models\Plan;
use App\Models\QuizAttempt;
use App\Models\Report;
use App\Models\Unlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrustTest extends TestCase
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

    private function caregiver(string $headline = 'Elderly caregiver', ?string $gcDate = null): User
    {
        $u = User::factory()->create(['role' => 'caregiver', 'phone' => '2547'.random_int(10000000, 99999999)]);
        CaregiverProfile::create(['user_id' => $u->id, 'headline' => $headline, 'care_types' => ['elderly'], 'arrangements' => ['day'],
            'county' => 'Nairobi', 'bio' => 'Kind.', 'photo_path' => 'p.jpg'])
            ->forceFill(['verification_status' => 'approved', 'verified_at' => now(), 'good_conduct_issued_on' => $gcDate ?? now()->subMonth()->toDateString()])->save();
        $u->subscriptions()->create(['plan_id' => Plan::where('audience', 'caregiver')->first()->id, 'starts_at' => now(), 'ends_at' => now()->addYear()]);
        Consent::grantAll($u);
        return $u;
    }

    private function family(): User
    {
        $u = User::factory()->create(['role' => 'family', 'phone' => '2547'.random_int(10000000, 99999999)]);
        FamilyProfile::create(['user_id' => $u->id, 'county' => 'Nairobi']);
        Consent::grantAll($u);
        return $u;
    }

    private function correctAnswers(string $skill, int $wrong = 0): array
    {
        $answers = [];
        foreach (config("skills.skills.$skill.quiz") as $i => $q) {
            $answers[$i] = $i < $wrong ? ($q['answer'] + 1) % count($q['options']) : $q['answer'];
        }
        return $answers;
    }

    public function test_expired_good_conduct_hides_caregiver_and_warns(): void
    {
        $this->caregiver('Fresh certificate');
        $old = $this->caregiver('Old certificate', now()->subMonths(7)->toDateString());
        $this->get('/caregivers')->assertSee('Fresh certificate')->assertDontSee('Old certificate');

        $expiring = $this->caregiver('Expiring certificate', now()->subMonths(5)->subDays(3)->toDateString());
        $this->assertSame('expiring', $expiring->caregiverProfile->goodConductState());
        $this->assertSame('expired', $old->caregiverProfile->goodConductState());
        $this->actingAs($expiring)->get('/caregiver')->assertSee('Check my new certificate');

        // Caregiver uploads a new certificate, asks for a re-check, admin records the new date
        $this->actingAs($expiring)->post('/caregiver/good-conduct')->assertSessionHasErrors('verify');
        $this->actingAs($expiring)->post('/my/documents', ['type' => 'good_conduct', 'file' => UploadedFile::fake()->create('gc.pdf', 40, 'application/pdf')]);
        $this->actingAs($expiring)->post('/caregiver/good-conduct')->assertSessionHasNoErrors();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post("/admin/verifications/{$old->id}/good-conduct", ['good_conduct_issued_on' => now()->subDays(2)->toDateString()])->assertSessionHasNoErrors();
        $this->get('/caregivers')->assertSee('Old certificate');
    }

    public function test_reports_pause_caregiver_and_admin_can_clear(): void
    {
        $cg = $this->caregiver('Reported caregiver');
        $f1 = $this->family();
        $f2 = $this->family();
        $this->actingAs($f1)->post("/report/{$cg->id}", ['reason' => 'no_show', 'details' => 'Did not come on the agreed day.'])->assertSessionHasNoErrors();
        $this->actingAs($f1)->post("/report/{$cg->id}", ['reason' => 'no_show', 'details' => 'Did not come again, second report.']);
        $this->assertSame(1, Report::count(), 'one open report per person');
        $this->get('/caregivers')->assertSee('Reported caregiver');

        $this->actingAs($f2)->post("/report/{$cg->id}", ['reason' => 'money', 'details' => 'Asked me for transport money upfront.'])->assertSessionHasNoErrors();
        $this->assertNotNull($cg->caregiverProfile->fresh()->paused_at);
        $this->get('/caregivers')->assertDontSee('Reported caregiver');
        $this->actingAs($cg)->post("/report/{$cg->id}", ['reason' => 'other', 'details' => 'Reporting myself here.'])->assertStatus(422);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/reports')->assertOk()->assertSee($cg->name);
        foreach (Report::all() as $r) {
            $this->actingAs($admin)->post("/admin/reports/{$r->id}", ['status' => 'dismissed', 'admin_note' => 'Spoke to both'])->assertSessionHasNoErrors();
        }
        $this->assertNull($cg->caregiverProfile->fresh()->paused_at);
        $this->get('/caregivers')->assertSee('Reported caregiver');
    }

    public function test_unsafe_report_from_a_client_family_pauses_immediately(): void
    {
        $cg = $this->caregiver();
        $stranger = $this->family();
        $this->actingAs($stranger)->post("/report/{$cg->id}", ['reason' => 'unsafe', 'details' => 'I heard they are rough.']);
        $this->assertNull($cg->caregiverProfile->fresh()->paused_at, 'a family with no dealings cannot pause alone');

        $client = $this->family();
        CareRequest::create(['family_id' => $client->id, 'caregiver_id' => $cg->id, 'care_type' => 'elderly', 'arrangement' => 'day', 'message' => 'x', 'status' => 'accepted']);
        $this->actingAs($client)->post("/report/{$cg->id}", ['reason' => 'unsafe', 'details' => 'Shouted at and pushed my mother.']);
        $this->assertNotNull($cg->caregiverProfile->fresh()->paused_at);
    }

    public function test_skill_quiz_pass_fail_and_cooldown(): void
    {
        $cg = $this->caregiver();
        $this->actingAs($cg)->get('/caregiver/skills')->assertOk()->assertSee('Dementia care');
        $page = $this->actingAs($cg)->get('/caregiver/skills/dementia_care/quiz')->assertOk();
        $page->assertDontSee('"answer"', false);

        // Fail with 3 wrong (7/10)
        $this->actingAs($cg)->post('/caregiver/skills/dementia_care/quiz', ['answers' => $this->correctAnswers('dementia_care', 3)])->assertOk()->assertSee('7/10');
        $this->assertFalse($cg->skills()->earned()->exists());
        $this->actingAs($cg)->get('/caregiver/skills/dementia_care/quiz')->assertRedirect(route('caregiver.skills'));
        $this->actingAs($cg)->post('/caregiver/skills/dementia_care/quiz', ['answers' => $this->correctAnswers('dementia_care')])->assertRedirect(route('caregiver.skills'));

        // After the wait, pass with 2 wrong (8/10)
        QuizAttempt::query()->update(['created_at' => now()->subHours(25)]);
        $this->actingAs($cg)->post('/caregiver/skills/dementia_care/quiz', ['answers' => $this->correctAnswers('dementia_care', 2)])->assertOk()->assertSee('You passed');
        $this->assertTrue($cg->skills()->where('skill', 'dementia_care')->first()->isTested());

        // Badge shows publicly and the skill filter finds them
        $this->get("/caregivers/{$cg->id}")->assertSee('Dementia care')->assertSee('tested');
        $this->get('/caregivers?skill=dementia_care')->assertSee('Elderly caregiver');
        $this->get('/caregivers?skill=first_aid')->assertSee('Other verified caregivers');
        $this->actingAs($cg)->get('/caregiver/skills/nonsense/quiz')->assertNotFound();
    }

    public function test_declared_skills_and_certificate_approval(): void
    {
        $cg = $this->caregiver();
        $this->actingAs($cg)->post('/caregiver/skills', ['skills' => ['first_aid', 'elderly_care']])->assertSessionHasNoErrors();
        $this->assertSame(2, $cg->skills()->count());
        $this->get("/caregivers/{$cg->id}")->assertSee('self-declared');

        $this->actingAs($cg)->post('/caregiver/skills/certificate', ['skill' => 'first_aid', 'file' => UploadedFile::fake()->create('fa.pdf', 40, 'application/pdf')])->assertSessionHasNoErrors();
        $row = $cg->skills()->where('skill', 'first_aid')->first();
        $this->assertSame('pending', $row->cert_status);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/skills')->assertOk()->assertSee('First aid');
        $this->actingAs($admin)->post("/admin/skills/{$row->id}", ['decision' => 'rejected'])->assertSessionHasErrors('cert_note');
        $this->actingAs($admin)->post("/admin/skills/{$row->id}", ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->assertTrue($row->fresh()->isCertified());
        $this->get("/caregivers/{$cg->id}")->assertSee('certificate checked');
        $this->actingAs($this->family())->post("/admin/skills/{$row->id}", ['decision' => 'approved'])->assertForbidden();
    }

    public function test_selfie_stays_private_from_families(): void
    {
        $cg = $this->caregiver();
        Storage::disk('local')->put('documents/s.jpg', 'x');
        Storage::disk('local')->put('documents/gc.pdf', 'x');
        $selfie = $cg->documents()->create(['type' => 'selfie', 'path' => 'documents/s.jpg', 'original_name' => 's.jpg']);
        $gc = $cg->documents()->create(['type' => 'good_conduct', 'path' => 'documents/gc.pdf', 'original_name' => 'gc.pdf']);
        $fam = $this->family();
        $pass = ContactPass::create(['user_id' => $fam->id, 'plan_id' => Plan::where('audience', 'family')->first()->id, 'credits_total' => 3, 'expires_at' => now()->addDays(30)]);
        Unlock::create(['family_id' => $fam->id, 'caregiver_id' => $cg->id, 'contact_pass_id' => $pass->id]);
        $this->actingAs($fam)->get("/documents/{$gc->id}")->assertOk();
        $this->actingAs($fam)->get("/documents/{$selfie->id}")->assertForbidden();
        $this->actingAs($fam)->get("/caregivers/{$cg->id}")->assertDontSee('Selfie holding');
    }

    public function test_trust_pages_load(): void
    {
        $cg = $this->caregiver();
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (['/admin', '/admin/reports', '/admin/reports?status=dismissed', '/admin/skills', "/admin/verifications/{$cg->id}"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        foreach (['/caregiver', '/caregiver/skills', '/my/documents'] as $url) {
            $this->actingAs($cg)->get($url)->assertOk();
        }
        $this->get("/caregivers/{$cg->id}")->assertSee('Why you can trust');
    }
}
