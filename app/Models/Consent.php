<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Evidence of what a user agreed to: exact wording, version, time, IP and device. */
class Consent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'type', 'version', 'statement', 'context_type', 'context_id', 'ip', 'user_agent'];

    public function user() { return $this->belongsTo(User::class); }

    public static function version(): string
    {
        return config('noc.consent_version');
    }

    public static function requiredFor(User $user): array
    {
        return match ($user->role) {
            'caregiver' => ['terms', 'data', 'caregiver_declaration', 'caregiver_independent'],
            'family' => ['terms', 'data', 'family_declaration'],
            default => [],
        };
    }

    public static function shareText(?string $role): string
    {
        return $role === 'caregiver'
            ? 'showing my photo, first name, skills and ratings to families, and sharing my contacts and documents with families who unlock them'
            : 'sharing my name, location and job details with caregivers I contact or who apply to my jobs';
    }

    public static function statement(string $type, ?User $user = null): string
    {
        $text = config('noc.consents.'.$type, $type);
        return $type === 'data' ? str_replace(':share', self::shareText($user?->role), $text) : $text;
    }

    public static function record(User $user, string $type, ?Request $request = null, ?Model $context = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => $type,
            'version' => self::version(),
            'statement' => self::statement($type, $user),
            'context_type' => $context ? class_basename($context) : null,
            'context_id' => $context?->getKey(),
            'ip' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : 'system',
        ]);
    }

    public static function missingFor(User $user): array
    {
        $have = self::where('user_id', $user->id)->where('version', self::version())->pluck('type')->all();
        return array_values(array_diff(self::requiredFor($user), $have));
    }

    public static function grantAll(User $user): void
    {
        foreach (self::missingFor($user) as $type) {
            self::record($user, $type);
        }
    }
}
