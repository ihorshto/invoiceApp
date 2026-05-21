<?php

namespace App\Enums;

enum DevisStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Accepted  = 'accepted';
    case Rejected  = 'rejected';
    case Converted = 'converted';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
