<?php

namespace Pharaonic\Hijri\Support;

final class JulianDay
{
    public static function fromGregorian(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + (12 * $a) - 3;

        return $day
            + intdiv((153 * $m) + 2, 5)
            + (365 * $y)
            + intdiv($y, 4)
            - intdiv($y, 100)
            + intdiv($y, 400)
            - 32045;
    }

    /**
     * @return array{year:int, month:int, day:int}
     */
    public static function toGregorian(int $julianDay): array
    {
        $a = $julianDay + 32044;
        $b = intdiv((4 * $a) + 3, 146097);
        $c = $a - intdiv(146097 * $b, 4);
        $d = intdiv((4 * $c) + 3, 1461);
        $e = $c - intdiv(1461 * $d, 4);
        $m = intdiv((5 * $e) + 2, 153);

        $day = $e - intdiv((153 * $m) + 2, 5) + 1;
        $month = $m + 3 - (12 * intdiv($m, 10));
        $year = (100 * $b) + $d - 4800 + intdiv($m, 10);

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
        ];
    }
}
