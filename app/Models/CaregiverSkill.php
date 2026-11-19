<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaregiverSkill extends Model
{
    protected $fillable = ['user_id', 'skill', 'declared', 'quiz_passed_at', 'quiz_score', 'cert_status', 'certificate_document_id', 'cert_note', 'certified_at', 'certified_by'];

    protected $casts = ['declared' => 'boolean', 'quiz_passed_at' => 'datetime', 'certified_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function certificate() { return $this->belongsTo(Document::class, 'certificate_document_id'); }

    /** Skills backed by a passed quiz or a checked certificate. */
    public function scopeEarned($q)
    {
        return $q->where(fn ($w) => $w->whereNotNull('quiz_passed_at')->orWhereNotNull('certified_at'));
    }

    public function label(): string
    {
        return config('skills.skills.'.$this->skill.'.label', ucfirst(str_replace('_', ' ', $this->skill)));
    }

    public function isTested(): bool { return $this->quiz_passed_at !== null; }
    public function isCertified(): bool { return $this->certified_at !== null; }
    public function isEarned(): bool { return $this->isTested() || $this->isCertified(); }

    /** "Certificate checked", "Tested" or "Self-declared". */
    public function level(): string
    {
        return $this->isCertified() ? 'certified' : ($this->isTested() ? 'tested' : 'declared');
    }
}
