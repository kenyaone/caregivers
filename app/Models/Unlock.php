<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unlock extends Model
{
    protected $fillable = ['family_id', 'caregiver_id', 'contact_pass_id'];

    public function family() { return $this->belongsTo(User::class, 'family_id'); }
    public function caregiver() { return $this->belongsTo(User::class, 'caregiver_id'); }
}
