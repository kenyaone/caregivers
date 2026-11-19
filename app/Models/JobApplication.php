<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = ['care_job_id', 'caregiver_id', 'message', 'status'];

    public function job() { return $this->belongsTo(CareJob::class, 'care_job_id'); }
    public function caregiver() { return $this->belongsTo(User::class, 'caregiver_id'); }
}
