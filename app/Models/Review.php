<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['family_id', 'caregiver_id', 'stars', 'comment'];

    public function family() { return $this->belongsTo(User::class, 'family_id'); }
}
