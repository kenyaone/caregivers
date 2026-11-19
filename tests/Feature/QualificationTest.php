<?php

namespace Tests\Feature;

use App\Models\CaregiverProfile;
use App\Models\Consent;
use App\Models\ContactPass;
use App\Models\FamilyProfile;
use App\Models\Plan;
use App\Models\Qualification;
use App\Models\Unlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QualificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        $this->seed();
    }

    private function caregiver(): User
    {
        $u = User::factory()->create(['role' => 'caregiver', 'phone' => '254711000111']);
        CaregiverProfile::create(['user_id' => $u->id, 'headline' => 'Carer', 'care_types' => ['elderly'], 'arrangements' => ['day'], 'county' => 'Nairobi', 'bio' => 'Kind.', 'photo_path' => 'p.jpg'])
            ->forceFill(['verification_status' => 'approved', 'verified_at' => now(), 'good_conduct_issued_on' => now()->subMonth()->toDateString()])->save();
        $u->subscriptions()->create(['plan_id' => Plan::where('audience', 'caregiver')->first()->id, 'starts_at' => now(), 'ends_at' => now()->addYear()]);
        Consent::grantAll($u);
        return $u;
    }

    public function test_caregiver_lists_qualifications_with_or_without_certificate(): void
    {
        $cg = $this->caregiver();
        $this->actingAs($cg)->get('/caregiver/qualifications')->assertOk();

        // Without a certificate
        $this->actingAs($cg)->post('/caregiver/qualifications', ['course' => 'Home-based care', 'institution' => 'Eldoret Training Centre', 'level' => 'short_course'])->assertSessionHasNoErrors();
        $noCert = Qualification::first();
        $this->assertSame('unverified', $noCert->status);

        // With a certificate
        $this->actingAs($cg)->post('/caregiver/qualifications', ['course' => 'Caregiving', 'institution' => 'Rift Valley College', 'level' => 'certificate', 'year' => 2023,
            'file' => UploadedFile::fake()->create('cert.pdf', 60, 'application/pdf')])->assertSessionHasNoErrors();
        $withCert = Qualification::where('course', 'Caregiving')->first();
        $this->assertSame('pending', $withCert->status);
        $this->assertSame('certificate', $withCert->document->type);

        // Bad input
        $this->actingAs($cg)->post('/caregiver/qualifications', ['course' => 'X', 'institution' => 'Y', 'level' => 'phd', 'year' => 2999])->assertSessionHasErrors(['level', 'year']);

        // Add the certificate later
        $this->actingAs($cg)->post("/caregiver/qualifications/{$noCert->id}/certificate", ['file' => UploadedFile::fake()->image('c.jpg')])->assertSessionHasNoErrors();
        $this->assertSame('pending', $noCert->fresh()->status);

        // Admin checks
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/qualifications')->assertOk()->assertSee('Rift Valley College');
        $this->actingAs($admin)->post("/admin/qualifications/{$withCert->id}", ['decision' => 'rejected'])->assertSessionHasErrors('note');
        $this->actingAs($admin)->post("/admin/qualifications/{$withCert->id}", ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->assertTrue($withCert->fresh()->isChecked());

        // Public profile shows it
        $this->get("/caregivers/{$cg->id}")->assertSee('Certificate in Caregiving')->assertSee('Rift Valley College')->assertSee('certificate checked');

        // Families who unlocked can open the certificate, others cannot edit someone else's entry
        $fam = User::factory()->create(['role' => 'family', 'phone' => '254722000111']);
        FamilyProfile::create(['user_id' => $fam->id, 'county' => 'Nairobi']);
        Consent::grantAll($fam);
        $pass = ContactPass::create(['user_id' => $fam->id, 'plan_id' => Plan::where('audience', 'family')->first()->id, 'credits_total' => 3, 'expires_at' => now()->addDays(30)]);
        Unlock::create(['family_id' => $fam->id, 'caregiver_id' => $cg->id, 'contact_pass_id' => $pass->id]);
        $this->actingAs($fam)->get("/documents/{$withCert->document_id}")->assertOk();

        $other = User::factory()->create(['role' => 'caregiver', 'phone' => '254733000111']);
        CaregiverProfile::create(['user_id' => $other->id]);
        Consent::grantAll($other);
        $this->actingAs($other)->delete("/caregiver/qualifications/{$withCert->id}")->assertForbidden();
        $this->actingAs($cg)->delete("/caregiver/qualifications/{$noCert->id}")->assertSessionHasNoErrors();
        $this->assertSame(1, Qualification::count());
    }
}
