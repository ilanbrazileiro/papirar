<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class DisplayDate
{
    public static function format(DateTimeInterface|string|null $value, string $format = 'd/m/Y H:i'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof DateTimeInterface
            ? Carbon::instance($value)
            : Carbon::parse($value, 'UTC');

        return $date->setTimezone(config('app.display_timezone', 'America/Sao_Paulo'))->format($format);
    }
}
