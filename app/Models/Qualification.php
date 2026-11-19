<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    public const LEVELS = [
        'short_course' => 'Short course',
        'certificate' => 'Certificate',
        'diploma' => 'Diploma',
        'degree' => 'Degree',
        'other' => 'Other',
    ];

    protected $fillable = ['user_id', 'course', 'institution', 'level', 'year', 'document_id'];

    protected $casts = ['checked_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function document() { return $this->belongsTo(Document::class); }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->level] ?? ucfirst($this->level);
    }

    public function isChecked(): bool
    {
        return $this->status === 'approved';
    }
}
