<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CaregiverProfile extends Model
{
    protected $guarded = ['id', 'verification_status', 'verification_note', 'verified_at', 'verified_by', 'verification_checks', 'rating_avg', 'rating_count'];

    protected $casts = [
        'care_types' => 'array',
        'arrangements' => 'array',
        'available' => 'boolean',
        'verified_at' => 'datetime',
        'verification_checks' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }

    /** Verified, subscribed and available caregivers that families can see. */
    public function scopeListed($q)
    {
        return $q->where('verification_status', 'approved')->where('available', true)
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
}
