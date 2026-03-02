<?php

namespace App\Enums;

enum AccountType: string
{
    case Personal = 'personal';
    case Contractor = 'contractor';
    case Agency = 'agency';
    case Enterprise = 'enterprise';
}
