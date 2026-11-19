<?php

namespace Tests\Feature;

use App\Models\Consent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_present(): void
    {
        $r = $this->get('/');
        $r->assertOk();
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('X-Frame-Options');
        $r->assertHeader('Referrer-Policy');
    }

    public function test_consent_required_before_using_the_app(): void
    {
        $u = User::factory()->create(['role' => 'caregiver']);
        $this->actingAs($u)->get('/caregiver')->assertRedirect(route('consent.show'));
        Consent::grantAll($u);
        $this->actingAs($u)->get('/caregiver')->assertOk();
    }

    public function test_roles_cannot_cross_into_other_areas(): void
    {
        $c = User::factory()->create(['role' => 'caregiver']);
        Consent::grantAll($c);
        $this->actingAs($c)->post('/admin/plans', ['name' => 'Hack', 'price' => 1, 'duration_days' => 1])->assertForbidden();
        $this->actingAs($c)->get('/family')->assertForbidden();

        $f = User::factory()->create(['role' => 'family']);
        Consent::grantAll($f);
        $this->actingAs($f)->get('/caregiver')->assertForbidden();
        $this->actingAs($f)->get('/admin')->assertForbidden();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/family/contacts')->assertRedirect('/login');
    }
}
