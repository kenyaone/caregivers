<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareRequest extends Model
{
    protected $fillable = ['family_id', 'caregiver_id', 'care_type', 'arrangement', 'start_date', 'message', 'status'];

    protected $casts = ['start_date' => 'date'];

    public function family() { return $this->belongsTo(User::class, 'family_id'); }
    public function caregiver() { return $this->belongsTo(User::class, 'caregiver_id'); }
}
