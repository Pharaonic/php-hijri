<?php

namespace Pharaonic\Hijri\Calendar;

final class HijriCalendar
{
    /**
     * Leap years are 2, 5, 7, 10, 13, 15, 18, 21, 24, 26 and 29 of each
     * 30-year cycle, the same as GregorianToHijriConverter.
     */
    public static function isLeapYear(int $year): bool
    {
        return ((11 * $year + 15) % 30) < 11;
    }

    public static function daysInMonth(int $year, int $month): int
    {
        if ($month < 1 || $month > 12) {
            return 0;
        }

        if ($month === 12) {
            return self::isLeapYear($year) ? 30 : 29;
        }

        return $month % 2 === 1 ? 30 : 29;
    }

    public static function isValidDate(int $year, int $month, int $day): bool
    {
        if ($year < 1) {
            return false;
        }

        $daysInMonth = self::daysInMonth($year, $month);

        return $daysInMonth > 0 && $day >= 1 && $day <= $daysInMonth;
    }
}
