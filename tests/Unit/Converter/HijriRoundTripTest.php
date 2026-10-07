<?php

namespace Pharaonic\Hijri\Tests\Unit\Converter;

use DateInterval;
use DateTimeImmutable;
use Pharaonic\Hijri\Calendar\HijriCalendar;
use Pharaonic\Hijri\Converter\GregorianToHijriConverter;
use Pharaonic\Hijri\Converter\HijriToGregorianConverter;
use PHPUnit\Framework\TestCase;

final class HijriRoundTripTest extends TestCase
{
    public function testLeapYearsMatchTheGregorianToHijriConverter(): void
    {
        $converter = new GregorianToHijriConverter();

        for ($year = 1; $year <= 3000; $year++) {
            self::assertSame(
                $converter->daysInYear($year) === 355,
                HijriCalendar::isLeapYear($year),
                (string) $year
            );
        }
    }

    public function testEveryDayConvertsBackToTheSameGregorianDate(): void
    {
        $toHijri = new GregorianToHijriConverter();
        $toGregorian = new HijriToGregorianConverter();
        $day = new DateTimeImmutable('1900-01-01');
        $end = new DateTimeImmutable('2100-12-31');
        $oneDay = new DateInterval('P1D');

        while ($day <= $end) {
            foreach ([0, -1] as $adjustment) {
                $hijri = $toHijri->convert($day, $adjustment);
                $gregorian = $toGregorian->convert($hijri['year'], $hijri['month'], $hijri['day'], $adjustment);

                self::assertSame(
                    $day->format('Y-m-d'),
                    sprintf('%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day']),
                    sprintf('%s, adjustment %d', $day->format('Y-m-d'), $adjustment)
                );
            }

            $day = $day->add($oneDay);
        }
    }

    public function testTheThirtiethOfDhuAlHijjahFollowsTheLeapYears(): void
    {
        $converter = new HijriToGregorianConverter();

        self::assertTrue(HijriCalendar::isValidDate(1425, 12, 30));
        self::assertFalse(HijriCalendar::isValidDate(1426, 12, 30));
        self::assertSame(['year' => 2005, 'month' => 2, 'day' => 9], $converter->convert(1425, 12, 30, 0));
        self::assertSame(['year' => 2005, 'month' => 2, 'day' => 10], $converter->convert(1426, 1, 1, 0));
    }
}
