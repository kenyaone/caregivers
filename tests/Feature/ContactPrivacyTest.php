<?php

namespace Tests\Feature;

use App\Models\CaregiverProfile;
use App\Models\Consent;
use App\Models\FamilyProfile;
use App\Models\Plan;
use App\Models\User;
use App\Rules\NoContactDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_detector_blocks_contacts_but_not_normal_numbers(): void
    {
        foreach (['Call 0712 345 678', '0712345678', '+254 712-345-678', '254712345678', '0110 123 456', 'me@gmail.com', 'me at gmail dot com', 'wa.me/254712345678', '07 12 34 56 78', '+44 7911 123456'] as $t) {
            $this->assertTrue(NoContactDetails::found($t), $t);
        }
        foreach (['8 years since 2016', 'KES 20,000 per month', 'Worked 2019 2020 2021', 'Mother, 81', '7 AM to 6 PM', 'Paid 15000 to 18000', null, ''] as $t) {
            $this->assertFalse(NoContactDetails::found($t), (string) $t);
        }
    }

    public function test_contacts_are_rejected_in_public_text_and_referee_stays_private(): void
    {
        Notification::fake();
        $this->seed();
        $cg = User::factory()->create(['role' => 'caregiver', 'phone' => '254711000222']);
        CaregiverProfile::create(['user_id' => $cg->id, 'headline' => 'Carer', 'care_types' => ['elderly'], 'arrangements' => ['day'], 'county' => 'Nairobi', 'bio' => 'Kind.',
            'photo_path' => 'p.jpg', 'referee_name' => 'Secret Referee', 'referee_phone' => '254799111222'])
            ->forceFill(['verification_status' => 'approved', 'verified_at' => now(), 'good_conduct_issued_on' => now()->subMonth()->toDateString()])->save();
        $cg->subscriptions()->create(['plan_id' => Plan::where('audience', 'caregiver')->first()->id, 'starts_at' => now(), 'ends_at' => now()->addYear()]);
        Consent::grantAll($cg);
        $fam = User::factory()->create(['role' => 'family', 'phone' => '254722000222']);
        FamilyProfile::create(['user_id' => $fam->id, 'county' => 'Nairobi']);
        Consent::grantAll($fam);

        $profile = ['name' => 'Grace', 'phone' => '0711000222', 'headline' => 'Carer', 'care_types' => ['elderly'], 'arrangements' => ['day'], 'county' => 'Nairobi', 'pay_unit' => 'per_month', 'available' => '1'];
        $this->actingAs($cg)->put('/caregiver/profile', $profile + ['bio' => 'Kind and patient. WhatsApp 0712 345 678'])->assertSessionHasErrors('bio');
        $this->actingAs($cg)->put('/caregiver/profile', $profile + ['bio' => 'Kind and patient, 8 years since 2016.'])->assertSessionHasNoErrors();

        $this->actingAs($fam)->post("/family/caregivers/{$cg->id}/request", ['care_type' => 'elderly', 'arrangement' => 'day', 'message' => 'Please call me on 0722 000 222 about my mum'])->assertSessionHasErrors('message');
        $job = ['title' => 'Carer for dad', 'care_type' => 'elderly', 'care_recipient' => 'Dad, 80', 'arrangement' => 'day', 'county' => 'Nairobi', 'town' => 'Westlands',
            'pay_amount' => 15000, 'pay_unit' => 'per_month', 'confirm' => '1'];
        $this->actingAs($fam)->post('/family/jobs', $job + ['description' => 'Email me at dad.care@gmail.com'])->assertSessionHasErrors('description');
        $this->actingAs($fam)->post('/family/jobs', $job + ['description' => 'Meals, bathing and walks, 8 AM to 5 PM.'])->assertSessionHasNoErrors();

        // Referee details never show publicly, even to families who unlocked
        $this->get("/caregivers/{$cg->id}")->assertDontSee('Secret Referee')->assertDontSee('254799111222');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get("/admin/verifications/{$cg->id}")->assertSee('Secret Referee');
    }
}
