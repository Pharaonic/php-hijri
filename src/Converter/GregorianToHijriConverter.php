<?php

namespace Pharaonic\Hijri\Converter;

use DateTimeImmutable;
use DateTimeInterface;
use Pharaonic\Hijri\Support\JulianDay;

final class GregorianToHijriConverter
{
    /**
     * Convert a Gregorian date to Hijri components.
     *
     * The algorithm is extracted from the original package implementation so
     * existing conversion behaviour remains stable while the public Carbon API
     * stays untouched.
     *
     * @return array{year:int, month:int, day:int}
     */
    public function convert(DateTimeInterface $date, int $adjustment = -1): array
    {
        $adjusted = DateTimeImmutable::createFromInterface($date);

        if ($adjustment !== 0) {
            $modifier = sprintf('%+d days', $adjustment);
            $adjusted = $adjusted->modify($modifier);
        }

        $year = (int) $adjusted->format('Y');
        $month = (int) $adjusted->format('n');
        $day = (int) $adjusted->format('j');

        $jd = JulianDay::fromGregorian($year, $month, $day);

        $y = 10631.0 / 30.0;
        $shift = 8.01 / 60.0;

        $z = $jd - 1948084;
        $cycle = (int) floor($z / 10631.0);
        $z -= 10631 * $cycle;

        $j = (int) floor(($z - $shift) / $y);
        $z -= floor(($j * $y) + $shift);

        $hijriYear = (30 * $cycle) + $j;
        $hijriMonth = (int) floor(($z + 28.5001) / 29.5);

        if ($hijriMonth === 13) {
            $hijriMonth = 12;
        }

        $hijriDay = (int) ($z - floor((29.5001 * $hijriMonth) - 29));

        return [
            'year' => $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay,
        ];
    }
}
