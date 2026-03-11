<?php

namespace App\Services;

class StaticHelpers
{
    public static function getConfigFlag(string | int | null $flag): bool
    {
        $log = strtolower(strval($flag));
        return in_array($log, ['1', 'yes', 'true', 'on']);
    }
}
