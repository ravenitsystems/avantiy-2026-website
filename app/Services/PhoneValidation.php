<?php

namespace App\Services;

class PhoneValidation
{
    /**
     * Validate that the string looks like a valid E.164 phone number (optional +, then 10-15 digits).
     */
    public static function isValidE164(string $telephone): bool
    {
        $normalized = preg_replace('/\s+/', '', trim($telephone));
        if ($normalized === '') {
            return true;
        }
        if (preg_match('/^\+?[0-9]{10,15}$/', $normalized) !== 1) {
            return false;
        }
        $digits = preg_replace('/\D/', '', $normalized);
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    public static function invalidPhoneMessage(): string
    {
        return 'Please enter a valid phone number.';
    }
}
