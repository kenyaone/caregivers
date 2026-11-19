<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Keeps phone numbers, emails and chat links out of text that other users can read,
 * so contacts are only shared through an unlock (and both sides stay protected).
 */
class NoContactDetails implements ValidationRule
{
    private const PATTERNS = [
        // Kenyan mobile/landline styles: 07.., 01.., 2547.., +254 7.., with spaces, dots or dashes in between
        '/(?<!\d)(?:\+?\s*254|0)[\s\-.()]*[17](?:[\s\-.()]*\d){8}(?!\d)/',
        // International numbers (+ then 9 or more digits) or any unbroken run of 10+ digits
        '/\+\s*\d(?:[\s\-.()]?\d){8,}/',
        '/(?<!\d)\d{10,}(?!\d)/',
        // Email addresses, including "name at gmail dot com"
        '/[A-Z0-9._%+\-]+\s*(?:@|\(at\)|\[at\]|\sat\s)\s*[A-Z0-9\-]+\s*(?:\.|\sdot\s)\s*(?:com|co\.ke|co|ke|net|org)\b/i',
        // Chat / social links
        '/\b(?:wa\.me|whatsapp\.com|chat\.whatsapp|t\.me|telegram\.me|m\.me|instagram\.com|facebook\.com|fb\.com|tiktok\.com)\b/i',
    ];

    public static function found(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }
        foreach (self::PATTERNS as $p) {
            if (preg_match($p, $text)) {
                return true;
            }
        }
        return false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && self::found($value)) {
            $fail('Please remove phone numbers, emails and WhatsApp or social links from this text. Contacts are shared safely through CareGivers254 once both sides connect.');
        }
    }
}
