<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['audience', 'name', 'price', 'duration_days', 'credits', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeFor($q, string $audience)
    {
        return $q->where('audience', $audience)->where('is_active', true)->orderBy('price');
    }
}
