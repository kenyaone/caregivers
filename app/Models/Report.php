<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'reported_id', 'care_job_id', 'reason', 'details'];

    protected $casts = ['resolved_at' => 'datetime'];

    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
    public function reported() { return $this->belongsTo(User::class, 'reported_id'); }
    public function job() { return $this->belongsTo(CareJob::class, 'care_job_id'); }

    public function reasonLabel(): string
    {
        return config('skills.report_reasons.'.$this->reason, $this->reason);
    }
}
