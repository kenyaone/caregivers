<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'amount', 'phone', 'method', 'reference', 'provider_location', 'status', 'raw', 'paid_at'];

    protected $casts = ['raw' => 'array', 'paid_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function plan() { return $this->belongsTo(Plan::class); }
}
