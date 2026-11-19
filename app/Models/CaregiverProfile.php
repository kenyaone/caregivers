<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CaregiverProfile extends Model
{
    protected $guarded = ['id', 'verification_status', 'verification_note', 'verified_at', 'verified_by', 'verification_checks', 'rating_avg', 'rating_count',
        'good_conduct_issued_on', 'good_conduct_warned_at', 'paused_at', 'paused_reason'];

    protected $casts = [
        'care_types' => 'array',
        'arrangements' => 'array',
        'available' => 'boolean',
        'verified_at' => 'datetime',
        'verification_checks' => 'array',
        'good_conduct_issued_on' => 'date',
        'good_conduct_warned_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function skills() { return $this->hasMany(CaregiverSkill::class, 'user_id', 'user_id'); }

    /** Verified, subscribed and available caregivers that families can see. */
    public function scopeListed($q)
    {
        return $q->where('verification_status', 'approved')->where('available', true)->whereNull('paused_at')
            ->whereDate('good_conduct_issued_on', '>', now()->subMonths(config('skills.good_conduct_valid_months'))->toDateString())
            ->whereHas('user', fn ($u) => $u->where('is_active', true)
                ->whereHas('subscriptions', fn ($s) => $s->where('ends_at', '>', now())));
    }

    public function isComplete(): bool
    {
        return $this->headline && $this->care_types && $this->arrangements && $this->county && $this->bio;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function careTypeLabels(): array
    {
        return array_values(array_intersect_key(config('noc.care_types'), array_flip($this->care_types ?? [])));
    }

    public function arrangementLabels(): array
    {
        return array_values(array_intersect_key(config('noc.arrangements'), array_flip($this->arrangements ?? [])));
    }

    public function payLabel(): string
    {
        if (! $this->expected_pay || $this->pay_unit === 'negotiable') {
            return 'Pay negotiable';
        }
        return 'From KES '.number_format($this->expected_pay).' '.(config('noc.pay_units')[$this->pay_unit] ?? '');
    }

    /** Date the Certificate of Good Conduct stops counting (6 months after issue). */
    public function goodConductValidUntil(): ?\Illuminate\Support\Carbon
    {
        return $this->good_conduct_issued_on?->copy()->addMonths(config('skills.good_conduct_valid_months'));
    }

    /** valid, expiring, expired or missing */
    public function goodConductState(): string
    {
        $until = $this->goodConductValidUntil();
        if (! $until) {
            return 'missing';
        }
        if ($until->isPast()) {
            return 'expired';
        }
        $warnFrom = $this->good_conduct_issued_on->copy()->addMonths(config('skills.good_conduct_warn_months'));
        return now()->greaterThanOrEqualTo($warnFrom) ? 'expiring' : 'valid';
    }

    /** Care that actually happened: accepted requests plus hires through jobs. */
    public function placementsCount(): int
    {
        return CareRequest::where('caregiver_id', $this->user_id)->where('status', 'accepted')->count()
            + JobApplication::where('caregiver_id', $this->user_id)->where('status', 'hired')->count();
    }
}
