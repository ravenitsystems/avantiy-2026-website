<?php

namespace App\Services;

class EmailValidation
{
    /**
     * Reject email if it contains '+' and APP_ENV is production.
     */
    public static function rejectEmailWithPlus(string $email): bool
    {
        if (config('app.env') !== 'production') {
            return false;
        }

        return str_contains($email, '+');
    }

    public static function rejectEmailWithPlusMessage(): string
    {
        return 'Email addresses containing "+" are not allowed in production.';
    }
}
