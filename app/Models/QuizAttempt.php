<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'skill', 'score', 'total', 'passed'];

    protected $casts = ['passed' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
