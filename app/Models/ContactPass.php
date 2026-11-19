<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPass extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'payment_id', 'credits_total', 'credits_used', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function plan() { return $this->belongsTo(Plan::class); }
}
