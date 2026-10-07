<?php

namespace Pharaonic\Hijri\Converter;

use DateTimeImmutable;
use Pharaonic\Hijri\Calendar\HijriCalendar;
use Pharaonic\Hijri\Exception\InvalidHijriDateException;
use Pharaonic\Hijri\Support\JulianDay;

final class HijriToGregorianConverter
{
    /**
     * @return array{year:int, month:int, day:int}
     */
    public function convert(int $year, int $month, int $day, int $adjustment = -1): array
    {
        if (! HijriCalendar::isValidDate($year, $month, $day)) {
            throw new InvalidHijriDateException(
                sprintf('Invalid Hijri date: %04d-%02d-%02d.', $year, $month, $day)
            );
        }

        // Days before the year count the same leap years as HijriCalendar.
        $julianDay = $day
            + (int) ceil(29.5 * ($month - 1))
            + (($year - 1) * 354)
            + (int) floor((4 + (11 * $year)) / 30)
            + 1948438;

        $gregorian = JulianDay::toGregorian($julianDay);

        if ($adjustment === 0) {
            return $gregorian;
        }

        $date = new DateTimeImmutable(sprintf(
            '%04d-%02d-%02d',
            $gregorian['year'],
            $gregorian['month'],
            $gregorian['day']
        ));

        $date = $date->modify(sprintf('%+d days', -$adjustment));

        return [
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('n'),
            'day' => (int) $date->format('j'),
        ];
    }
}
