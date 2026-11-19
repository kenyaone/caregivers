<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareJob extends Model
{
    protected $fillable = ['title', 'care_type', 'care_recipient', 'arrangement', 'county', 'town', 'start_date', 'pay_amount', 'pay_unit', 'description', 'requirements', 'status'];

    protected $casts = ['start_date' => 'date'];

    public function family() { return $this->belongsTo(User::class, 'family_id'); }
    public function applications() { return $this->hasMany(JobApplication::class); }

    public function scopeOpen($q) { return $q->where('status', 'open'); }

    public function careTypeLabel(): string { return config('noc.care_types')[$this->care_type] ?? $this->care_type; }
    public function arrangementLabel(): string { return config('noc.arrangements')[$this->arrangement] ?? $this->arrangement; }

    public function payLabel(): string
    {
        if (! $this->pay_amount || $this->pay_unit === 'negotiable') {
            return 'Pay negotiable';
        }
        return 'KES '.number_format($this->pay_amount).' '.(config('noc.pay_units')[$this->pay_unit] ?? '');
    }
}
