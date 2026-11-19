<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** Only the owner and admins ever see these. */
    public const PRIVATE_TYPES = ['national_id', 'selfie'];

    protected $fillable = ['user_id', 'type', 'path', 'original_name'];

    public function user() { return $this->belongsTo(User::class); }

    public function label(): string
    {
        $types = config('noc.document_types.caregiver') + config('noc.document_types.family');
        return $types[$this->type] ?? ucfirst($this->type);
    }
}
