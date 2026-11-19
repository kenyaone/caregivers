<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = ['role' => 'family', 'is_active' => true];

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function isCaregiver(): bool { return $this->role === 'caregiver'; }
    public function isFamily(): bool { return $this->role === 'family'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }

    public function caregiverProfile() { return $this->hasOne(CaregiverProfile::class)->withDefault(); }
    public function familyProfile() { return $this->hasOne(FamilyProfile::class)->withDefault(); }
    public function documents() { return $this->hasMany(Document::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
    public function contactPasses() { return $this->hasMany(ContactPass::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function consents() { return $this->hasMany(Consent::class); }
    public function careJobs() { return $this->hasMany(CareJob::class, 'family_id'); }
    public function applications() { return $this->hasMany(JobApplication::class, 'caregiver_id'); }
    public function skills() { return $this->hasMany(CaregiverSkill::class); }
    public function qualifications() { return $this->hasMany(Qualification::class); }

    /** A family "worked with" a caregiver once a care request was accepted or the caregiver was hired for one of its jobs. */
    public function hasWorkedWith(User $caregiver): bool
    {
        return CareRequest::where('family_id', $this->id)->where('caregiver_id', $caregiver->id)->where('status', 'accepted')->exists()
            || JobApplication::where('caregiver_id', $caregiver->id)->where('status', 'hired')
                ->whereHas('job', fn ($q) => $q->where('family_id', $this->id))->exists();
    }

    public function profile()
    {
        return $this->isCaregiver() ? $this->caregiverProfile : $this->familyProfile;
    }

    public function displayName(): string
    {
        return $this->name;
    }

    /** "Grace N." – what the public sees before contacts are unlocked. */
    public function publicName(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        return $parts[0].(isset($parts[1]) ? ' '.mb_strtoupper(mb_substr(end($parts), 0, 1)).'.' : '');
    }

    public function isVerified(): bool
    {
        return $this->isAdmin() || $this->profile()?->verification_status === 'approved';
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()->where('ends_at', '>', now())->orderByDesc('ends_at')->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /** Contact pass with credits left, soonest-expiring first. */
    public function usablePass(): ?ContactPass
    {
        return $this->contactPasses()->where('expires_at', '>', now())
            ->whereColumn('credits_used', '<', 'credits_total')->orderBy('expires_at')->first();
    }

    public function creditsLeft(): int
    {
        return (int) $this->contactPasses()->where('expires_at', '>', now())
            ->selectRaw('COALESCE(SUM(credits_total - credits_used), 0) as left_count')->value('left_count');
    }

    public function hasUnlocked(User $caregiver): bool
    {
        return Unlock::where('family_id', $this->id)->where('caregiver_id', $caregiver->id)->exists();
    }

    public function routeNotificationForSms(): ?string
    {
        return $this->phone ? self::normalisePhone($this->phone) : null;
    }

    public static function normalisePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '254'.substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '254'.$digits;
        }
        return $digits;
    }
}
