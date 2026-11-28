<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Iso
{
    public static function format(?CarbonInterface $date): ?string
    {
        return $date?->copy()->utc()->format('Y-m-d\TH:i:s.v\Z');
    }
}
