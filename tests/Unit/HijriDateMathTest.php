<?php

namespace Pharaonic\Hijri\Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Pharaonic\Hijri\Calendar\HijriCalendar;
use Pharaonic\Hijri\Converter\HijriToGregorianConverter;
use Pharaonic\Hijri\Hijri;
use Pharaonic\Hijri\HijriCarbon;
use PHPUnit\Framework\TestCase;

final class HijriDateMathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::mixin(HijriCarbon::class);
        Hijri::getInstance()->setHijriAdjustment(-1);
    }

    protected function tearDown(): void
    {
        Hijri::getInstance()->setHijriAdjustment(-1);

        parent::tearDown();
    }

    public function testTheInstanceHoldsTheGregorianDate(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 08:30:00', 'UTC');

        self::assertSame('1445-09-01', $date->format('Y-m-d'));
        self::assertSame('2024-03-11 08:30:00', $date->rawFormat('Y-m-d H:i:s'));
        self::assertSame(Carbon::parse('2024-03-11 08:30:00', 'UTC')->getTimestamp(), $date->getTimestamp());
        self::assertSame(Carbon::parse('2024-03-11 08:30:00', 'UTC')->getTimestamp(), $date->timestamp);
        self::assertSame((string) $date->getTimestamp(), $date->format('U'));
    }

    public function testComparisonsWithGregorianDates(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 12:00', 'UTC');

        self::assertEquals(0, $date->diffInDays(Carbon::parse('2024-03-11 12:00', 'UTC')));
        self::assertEquals(10, $date->diffInDays(Carbon::parse('2024-03-21 12:00', 'UTC'), true));
        self::assertEquals(36, $date->diffInHours(Carbon::parse('2024-03-13 00:00', 'UTC'), true));
        self::assertTrue($date->eq(Carbon::parse('2024-03-11 12:00', 'UTC')));
        self::assertTrue($date->lt(Carbon::parse('2024-03-12', 'UTC')));
        self::assertTrue($date->gt(Carbon::parse('2024-03-10', 'UTC')));
        self::assertTrue($date->between(Carbon::parse('2024-03-10', 'UTC'), Carbon::parse('2024-03-12', 'UTC')));
        self::assertTrue($date->isPast());
        self::assertFalse($date->isFuture());
        self::assertTrue(Carbon::parse('2024-03-11 12:00', 'UTC')->eq($date));
        self::assertTrue($date->isSameDay(Carbon::parse('2024-03-11', 'UTC')));
    }

    public function testDayMathFollowsTheRealDate(): void
    {
        $date = Hijri::fromGregorian('2023-09-15');

        self::assertSame('1445-02-29', $date->format('Y-m-d'));
        self::assertSame('1445-03-01', $date->copy()->addDay()->format('Y-m-d'));
        self::assertSame('1445-02-28', $date->copy()->subDay()->format('Y-m-d'));
        self::assertSame('1445-03-07', $date->copy()->addWeek()->format('Y-m-d'));
        self::assertSame('1445-03-01', $date->copy()->addHours(24)->format('Y-m-d'));
    }

    public function testEveryDayAddsUpToTheNextDay(): void
    {
        $day = new DateTimeImmutable('2019-01-01');
        $end = new DateTimeImmutable('2026-12-31');
        $oneDay = new DateInterval('P1D');

        while ($day <= $end) {
            $next = $day->add($oneDay);

            self::assertSame(
                Hijri::fromGregorian($next)->format('Y-m-d'),
                Hijri::fromGregorian($day)->addDay()->format('Y-m-d'),
                $day->format('Y-m-d')
            );

            $day = $next;
        }
    }

    public function testMonthMathFromTheReport(): void
    {
        $date = Hijri::fromGregorian('2023-09-15');

        self::assertSame('1445-03-29', $date->copy()->addMonth()->format('Y-m-d'));
        self::assertSame('1445-01-29', $date->copy()->subMonth()->format('Y-m-d'));
        self::assertSame('1446-02-29', $date->copy()->addYear()->format('Y-m-d'));
    }

    public function testAddingMonthsInTheHijriCalendar(): void
    {
        $converter = new HijriToGregorianConverter();
        $day = new DateTimeImmutable('2022-01-01 13:00:00');
        $end = new DateTimeImmutable('2025-12-31');
        $fiveDays = new DateInterval('P5D');

        while ($day <= $end) {
            $date = Hijri::fromGregorian($day);

            foreach ([-25, -13, -1, 1, 2, 11, 12, 30] as $months) {
                $total = ($date->year * 12) + $date->month - 1 + $months;
                $year = intdiv($total, 12);
                $month = $total % 12 + 1;
                $daysInMonth = HijriCalendar::daysInMonth($year, $month);
                $expected = $converter->convert($year, $month, min($date->day, $daysInMonth), -1);
                $label = sprintf('%s %+d months', $date->format('Y-m-d'), $months);

                $result = $date->copy()->addMonthsNoOverflow($months);

                self::assertSame(
                    vsprintf('%04d-%02d-%02d 13:00:00', $expected),
                    $result->rawFormat('Y-m-d H:i:s'),
                    $label
                );
                self::assertSame([$year, $month], [$result->year, $result->month], $label);

                $overflow = $date->copy()->addMonthsWithOverflow($months);

                if ($date->day <= $daysInMonth) {
                    self::assertSame($result->rawFormat('Y-m-d'), $overflow->rawFormat('Y-m-d'), $label);
                } else {
                    self::assertSame(1, $overflow->day, $label);
                    self::assertSame(
                        $result->copy()->addDay()->rawFormat('Y-m-d'),
                        $overflow->rawFormat('Y-m-d'),
                        $label
                    );
                }
            }

            $day = $day->add($fiveDays);
        }
    }

    public function testOverflowOfTheThirtiethDay(): void
    {
        // 1445-01-30: Safar 1445 has 29 days.
        $date = Hijri::fromGregorian('2023-08-17');

        self::assertSame('1445-01-30', $date->format('Y-m-d'));
        self::assertSame('1445-02-29', $date->copy()->addMonthNoOverflow()->format('Y-m-d'));
        self::assertSame('1445-03-01', $date->copy()->addMonthWithOverflow()->format('Y-m-d'));
        self::assertSame('1445-02-29', $date->copy()->addMonthsNoOverflow(1)->format('Y-m-d'));

        // 1445-12-30 (leap year) to 1446-12, which has 29 days.
        $date = Hijri::fromGregorian('2024-07-07');

        self::assertSame('1445-12-30', $date->format('Y-m-d'));
        self::assertSame('1446-12-29', $date->copy()->addYearNoOverflow()->format('Y-m-d'));
        self::assertSame('1447-01-01', $date->copy()->addYearWithOverflow()->format('Y-m-d'));
    }

    public function testOtherUnitsAreHijriToo(): void
    {
        $date = Hijri::fromGregorian('2024-03-11');

        self::assertSame('1445-12-01', $date->copy()->addQuarter()->format('Y-m-d'));
        self::assertSame('1455-09-01', $date->copy()->addDecade()->format('Y-m-d'));
        self::assertSame('1545-09-01', $date->copy()->addCentury()->format('Y-m-d'));
        self::assertSame('1446-09-01', $date->copy()->add(1, 'year')->format('Y-m-d'));
        self::assertSame('1445-10-01', $date->copy()->add('month', 1)->format('Y-m-d'));
        self::assertSame('1445-08-01', $date->copy()->sub(1, 'month')->format('Y-m-d'));
    }

    public function testIntervalsAreHijri(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 10:00');

        self::assertSame('1445-11-01', $date->copy()->add(CarbonInterval::months(2))->format('Y-m-d'));
        self::assertSame('1445-10-03 10:00', $date->copy()->add(new DateInterval('P1M2D'))->format('Y-m-d H:i'));
        self::assertSame('1444-09-01', $date->copy()->sub(new DateInterval('P1Y'))->format('Y-m-d'));
        self::assertSame(
            '1446-10-01 12:30',
            $date->copy()->add('1 year 1 month 2 hours 30 minutes')->format('Y-m-d H:i')
        );
        self::assertSame('1445-08-01', $date->copy()->sub(CarbonInterval::month())->format('Y-m-d'));

        $interval = new DateInterval('P1M');
        $interval->invert = 1;

        self::assertSame('1445-08-01', $date->copy()->add($interval)->format('Y-m-d'));

        $months = [];

        foreach (CarbonPeriod::create($date, '1 month', 3) as $month) {
            $months[] = $month->format('Y-m-d');
        }

        self::assertSame(['1445-09-01', '1445-10-01', '1445-11-01'], $months);
    }

    public function testSettingHijriParts(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 09:15');

        self::assertSame('1446-09-01 09:15', $date->copy()->setYear(1446)->format('Y-m-d H:i'));
        self::assertSame('1445-10-01', $date->copy()->month(10)->format('Y-m-d'));
        self::assertSame('1445-09-15', $date->copy()->day(15)->format('Y-m-d'));
        self::assertSame('1445-09-15', $date->copy()->setDay(15)->format('Y-m-d'));
        self::assertSame('1440-02-01', $date->copy()->set(['year' => 1440, 'month' => 2])->format('Y-m-d'));
        self::assertSame('1440-02-10 09:15', $date->copy()->setDate(1440, 2, 10)->format('Y-m-d H:i'));
        self::assertSame('1440-02-10 07:00', $date->copy()->setDateTime(1440, 2, 10, 7, 0)->format('Y-m-d H:i'));
        self::assertSame('1445-01-01', $date->copy()->dayOfYear(1)->format('Y-m-d'));

        $changed = $date->copy();
        $changed->year = 1446;
        $changed->month = 1;
        $changed->day = 10;

        self::assertSame('1446-01-10', $changed->format('Y-m-d'));
        self::assertSame('2024-07-17', $changed->toGregorian()->format('Y-m-d'));
    }

    public function testSettingADayOutOfRangeMovesTheDate(): void
    {
        $date = Hijri::fromGregorian('2024-03-11');

        // Safar 1445 has 29 days.
        self::assertSame('1445-03-01', $date->copy()->setDate(1445, 2, 30)->format('Y-m-d'));
        self::assertSame('1445-01-30', $date->copy()->setDate(1445, 2, 0)->format('Y-m-d'));
        self::assertSame('1446-01-01', $date->copy()->setDate(1445, 13, 1)->format('Y-m-d'));
        self::assertSame('1444-12-01', $date->copy()->setDate(1445, 0, 1)->format('Y-m-d'));
    }

    public function testBoundaries(): void
    {
        $date = Hijri::fromGregorian('2024-03-21 15:00');

        self::assertSame('1445-09-01 00:00:00', $date->copy()->startOfMonth()->format('Y-m-d H:i:s'));
        self::assertSame('1445-09-30 23:59:59', $date->copy()->endOfMonth()->format('Y-m-d H:i:s'));
        self::assertSame('1445-07-01', $date->copy()->startOfQuarter()->format('Y-m-d'));
        self::assertSame('1445-09-30', $date->copy()->endOfQuarter()->format('Y-m-d'));
        self::assertSame('1445-01-01 00:00:00', $date->copy()->startOfYear()->format('Y-m-d H:i:s'));
        self::assertSame('1445-12-30 23:59:59', $date->copy()->endOfYear()->format('Y-m-d H:i:s'));
        self::assertSame('1440-01-01', $date->copy()->startOfDecade()->format('Y-m-d'));
        self::assertSame('1449-12-29', $date->copy()->endOfDecade()->format('Y-m-d'));
        self::assertSame('1401-01-01', $date->copy()->startOfCentury()->format('Y-m-d'));
        self::assertSame('1500-12-29', $date->copy()->endOfCentury()->format('Y-m-d'));
        self::assertSame('1001-01-01', $date->copy()->startOfMillennium()->format('Y-m-d'));
        self::assertSame('2000-12-29', $date->copy()->endOfMillennium()->format('Y-m-d'));
        self::assertSame('2023-07-19', $date->copy()->startOfYear()->toGregorian()->format('Y-m-d'));
        self::assertSame('1445-09-01', $date->copy()->startOf('month')->format('Y-m-d'));
        self::assertSame('1445-09-30', $date->copy()->endOf('month')->format('Y-m-d'));
    }

    public function testFirstLastAndNthWeekdays(): void
    {
        $date = Hijri::fromGregorian('2024-03-21');

        self::assertSame('1445-09-01', $date->copy()->firstOfMonth()->format('Y-m-d'));
        self::assertSame('1445-09-30', $date->copy()->lastOfMonth()->format('Y-m-d'));
        self::assertSame('1445-09-05 Friday', $date->copy()->firstOfMonth(Carbon::FRIDAY)->format('Y-m-d l'));
        self::assertSame('1445-09-26 Friday', $date->copy()->lastOfMonth(Carbon::FRIDAY)->format('Y-m-d l'));
        self::assertSame('1445-09-01 Monday', $date->copy()->firstOfMonth(Carbon::MONDAY)->format('Y-m-d l'));
        self::assertSame('1445-09-12 Friday', $date->copy()->nthOfMonth(2, Carbon::FRIDAY)->format('Y-m-d l'));
        self::assertFalse($date->copy()->nthOfMonth(6, Carbon::MONDAY));
        self::assertSame('1445-07-02 Saturday', $date->copy()->nthOfQuarter(1, Carbon::SATURDAY)->format('Y-m-d l'));
        self::assertSame('1445-01-05 Sunday', $date->copy()->nthOfYear(1, Carbon::SUNDAY)->format('Y-m-d l'));
        self::assertSame('1445-01-01', $date->copy()->firstOfYear()->format('Y-m-d'));
        self::assertSame('1445-12-30', $date->copy()->lastOfYear()->format('Y-m-d'));
        self::assertSame('1445-07-01', $date->copy()->firstOfQuarter()->format('Y-m-d'));
        self::assertSame('1445-09-30', $date->copy()->lastOfQuarter()->format('Y-m-d'));
    }

    public function testHijriValues(): void
    {
        $date = Hijri::fromGregorian('2024-03-21');

        self::assertSame(1445, $date->year);
        self::assertSame(9, $date->month);
        self::assertSame(11, $date->day);
        self::assertSame(30, $date->daysInMonth);
        self::assertSame(247, $date->dayOfYear);
        self::assertSame(355, $date->daysInYear);
        self::assertTrue($date->isLeapYear());
        self::assertSame(3, $date->quarter);
        self::assertSame(145, $date->decade);
        self::assertSame(15, $date->century);
        self::assertSame(2, $date->weekOfMonth);
        self::assertSame('Ramadan', $date->englishMonth);
        self::assertSame(4, $date->dayOfWeek);
        self::assertTrue($date->isThursday());
        self::assertTrue($date->copy()->endOfMonth()->isLastOfMonth());
        self::assertFalse($date->isLastOfMonth());
        self::assertFalse(Hijri::fromGregorian('2024-07-08')->isLeapYear());
    }

    public function testDifferencesInHijriMonthsAndYears(): void
    {
        $date = Hijri::fromGregorian('2024-03-11');

        self::assertEquals(12, $date->diffInMonths($date->copy()->addYear(), true));
        self::assertEquals(1, $date->diffInMonths(Carbon::parse('2024-04-10'), true));
        self::assertEquals(0, (int) $date->diffInMonths(Carbon::parse('2024-04-09'), true));
        self::assertEquals(12, (int) $date->diffInMonths(Carbon::parse('2025-03-01'), true));
        self::assertEquals(4, (int) $date->diffInQuarters(Carbon::parse('2025-03-01'), true));
        self::assertEquals(34, (int) $date->diffInYears(Carbon::parse('2057-03-11'), true));
        // 2056-03-11 is 1478-08-23.
        self::assertEquals(32, (int) $date->diffInYears(Carbon::parse('2056-03-11'), true));
        self::assertEquals(1, (int) $date->diffInYears($date->copy()->addYear(), true));
        self::assertEquals(1, (int) Hijri::fromGregorian('2025-03-01')->diffInYears($date, true));

        Carbon::setTestNow('2026-10-07');

        try {
            // 1990-01-01 is 1410-06-04; 2026-10-07 is 1448-04-25.
            self::assertSame(37, Hijri::fromGregorian('1990-01-01')->age);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function testSameUnitChecksAreHijri(): void
    {
        $date = Hijri::fromGregorian('2024-03-11');

        self::assertTrue($date->isSameMonth(Carbon::parse('2024-04-09')));
        self::assertFalse($date->isSameMonth(Carbon::parse('2024-04-10')));
        self::assertFalse($date->isSameMonth(Carbon::parse('2024-03-10')));
        self::assertTrue($date->isSameYear(Carbon::parse('2024-07-06')));
        self::assertFalse($date->isSameYear(Carbon::parse('2024-07-08')));
        self::assertTrue($date->isSameQuarter(Carbon::parse('2024-01-13')));
        self::assertFalse($date->isSameQuarter(Carbon::parse('2024-04-10')));
        self::assertTrue($date->isSameUnit('month', Carbon::parse('2024-03-20')));
        self::assertTrue($date->isBirthday(Carbon::parse('2025-03-01')));
        self::assertFalse($date->isBirthday(Carbon::parse('2025-03-11')));
        self::assertTrue(Hijri::now()->isCurrentMonth());
        self::assertTrue(Hijri::now()->isCurrentYear());
        self::assertTrue(Hijri::now()->addMonth()->isNextMonth());
    }

    public function testTheAdjustmentOfTheInstanceIsKept(): void
    {
        $date = Hijri::fromGregorian('2024-03-11', null, 1);

        self::assertSame('1445-09-03', $date->format('Y-m-d'));
        self::assertSame('1445-10-03', $date->copy()->addMonth()->format('Y-m-d'));
        self::assertSame('2024-04-10', $date->copy()->addMonth()->toGregorian()->format('Y-m-d'));
        self::assertSame('2024-03-09', $date->copy()->startOfMonth()->toGregorian()->format('Y-m-d'));

        Hijri::getInstance()->setHijriAdjustment(0);

        self::assertSame('1445-09-03', $date->format('Y-m-d'));
        self::assertSame('1445-09-02', Hijri::fromGregorian('2024-03-11')->format('Y-m-d'));
        self::assertSame(Hijri::fromGregorian('now')->format('Y-m-d'), Hijri::now()->format('Y-m-d'));
        self::assertTrue(Hijri::fromGregorian('now', null, 2)->isToday());
        self::assertTrue(Hijri::fromGregorian('yesterday', null, 2)->isYesterday());
        self::assertTrue(Hijri::fromGregorian('tomorrow', null, 2)->isTomorrow());
    }

    public function testTimezones(): void
    {
        $date = Hijri::fromGregorian('2024-03-10 23:00', 'UTC');

        self::assertSame('1445-08-29', $date->format('Y-m-d'));
        self::assertSame('1445-09-01 02:00', $date->copy()->setTimezone('Asia/Riyadh')->format('Y-m-d H:i'));
        self::assertSame($date->getTimestamp(), $date->copy()->setTimezone('Asia/Riyadh')->getTimestamp());

        $shifted = Hijri::fromGregorian('2024-03-11 10:00', 'UTC')->shiftTimezone('Asia/Riyadh');

        self::assertSame('1445-09-01 10:00 Asia/Riyadh', $shifted->format('Y-m-d H:i e'));
        self::assertSame('2024-03-11T10:00:00+03:00', $shifted->toGregorian()->format('c'));
    }

    public function testFromGregorianKeepsTheExactInstant(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('PHP 8.0 loses the second of two repeated hours in DateTime::setTimezone().');
        }

        // 01:30 happens twice in New York on 2024-11-03; this is the second one.
        $repeated = (new DateTime('@1730615400'))->setTimezone(new DateTimeZone('America/New_York'));
        $date = Hijri::fromGregorian($repeated);

        self::assertSame(1730615400, $date->getTimestamp());
        self::assertSame('1446-05-01 01:30 America/New_York', $date->format('Y-m-d H:i e'));
        self::assertSame(1730615400, $date->toGregorian()->getTimestamp());
        self::assertSame('2024-11-03 06:30', Hijri::fromGregorian($repeated, 'UTC')->rawFormat('Y-m-d H:i'));
    }

    public function testStrings(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 08:30:15', 'UTC')->locale('en');

        self::assertSame('1445-09-01 08:30:15', (string) $date);
        self::assertSame('1445-09-01', $date->toDateString());
        self::assertSame('1445-09-01 08:30:15', $date->toDateTimeString());
        self::assertSame('1445-09-01 08:30', $date->toDateTimeString('minute'));
        self::assertSame('Ramadan 1, 1445', $date->toFormattedDateString());
        self::assertSame('Mon, Ramadan 1, 1445', $date->toFormattedDayDateString());
        self::assertSame('Mon, Ramadan 1, 1445 8:30 AM', $date->toDayDateTimeString());
        self::assertSame('1445-09-01T08:30:15+00:00', $date->format('c'));
        self::assertSame('2024-03-11T08:30:15.000000Z', $date->toISOString());
        self::assertSame('"2024-03-11T08:30:15.000000Z"', json_encode($date));
        self::assertSame('2024-03-11T08:30:15+00:00', $date->toAtomString());

        $array = $date->toArray();

        self::assertSame([1445, 9, 1, 1], [$array['year'], $array['month'], $array['day'], $array['dayOfWeek']]);
        self::assertSame('1445-09-01 08:30:15', $array['formatted']);
    }

    public function testInstancesMadeByCarbonAreHijri(): void
    {
        self::assertSame('1445-09-01', Hijri::create(2024, 3, 11)->format('Y-m-d'));
        self::assertSame('1445-09-01', Hijri::createFromDate(2024, 3, 11)->format('Y-m-d'));
        self::assertSame('1445-09-01', Hijri::createFromFormat('d/m/Y', '11/03/2024')->format('Y-m-d'));
        self::assertSame('Ramadan', Hijri::create(2024, 3, 11)->locale('en')->format('F'));
        self::assertSame(
            Carbon::now()->toHijri()->format('Y-m-d F'),
            Hijri::now()->format('Y-m-d F')
        );
    }

    public function testCopiesAndSerialization(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 08:30:15.250000', 'Asia/Riyadh', 1);
        $copy = unserialize(serialize($date));

        self::assertInstanceOf(Hijri::class, $copy);
        self::assertSame('1445-09-03 08:30:15.250000 Asia/Riyadh', $copy->format('Y-m-d H:i:s.u e'));
        self::assertSame('2024-03-11', $copy->toGregorian()->format('Y-m-d'));

        $immutable = $date->toImmutable();

        self::assertInstanceOf(CarbonImmutable::class, $immutable);
        self::assertSame('2024-03-11 08:30:15', $immutable->format('Y-m-d H:i:s'));
        self::assertSame('1445-09-03', Carbon::instance($date)->format('Y-m-d'));
        self::assertSame('1445-09-02', $date->toHijri(0)->format('Y-m-d'));
    }
}
