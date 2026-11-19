<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyProfile extends Model
{
    protected $guarded = ['id', 'verification_status', 'verification_note', 'verified_at', 'verified_by', 'verification_checks'];

    protected $casts = ['verified_at' => 'datetime', 'verification_checks' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    public function isComplete(): bool
    {
        return (bool) ($this->county && $this->town);
    }
}
