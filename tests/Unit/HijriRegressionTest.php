<?php

namespace Pharaonic\Hijri\Tests\Unit;

use Carbon\Carbon;
use Carbon\AbstractTranslator;
use DateInterval;
use DateTimeImmutable;
use Pharaonic\Hijri\Converter\GregorianToHijriConverter;
use Pharaonic\Hijri\Exception\InvalidHijriDateException;
use Pharaonic\Hijri\Hijri;
use Pharaonic\Hijri\HijriCarbon;
use PHPUnit\Framework\TestCase;

final class HijriRegressionTest extends TestCase
{
    /** @var string */
    private $locale;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::mixin(HijriCarbon::class);
        Hijri::getInstance()->setHijriAdjustment(-1);

        $this->locale = Carbon::getLocale();
    }

    protected function tearDown(): void
    {
        Carbon::setLocale($this->locale);

        parent::tearDown();
    }

    public function testHijriConversionsDoNotChangeGregorianMonthNames(): void
    {
        $before = $this->formatGregorian('en');

        Carbon::parse('2024-03-11')->toHijri()->locale('ar');
        Carbon::parse('2024-03-11')->toHijri()->locale('en');

        self::assertSame('11 March 2024', $before);
        self::assertSame('11 March 2024', $this->formatGregorian('en'));
        self::assertSame('11 مارس 2024', $this->formatGregorian('ar'));
        self::assertSame('11 March 2024', $this->formatGregorian());
        self::assertSame('March', Carbon::parse('2024-03-11')->translatedFormat('F'));
        self::assertSame('March', Carbon::parse('2024-03-11')->monthName);
    }

    public function testHijriConversionsDoNotChangeGregorianMonthNamesOfTheGlobalLocale(): void
    {
        Carbon::setLocale('ar');

        self::assertSame('1 رَمضان 1445', Carbon::parse('2024-03-11')->toHijri()->isoFormat('D MMMM YYYY'));
        Carbon::parse('2024-03-11')->toHijri()->locale('en');

        self::assertSame('11 مارس 2024', $this->formatGregorian());
        self::assertSame('11 مارس 2024', $this->formatGregorian('ar'));
        self::assertSame('11 March 2024', $this->formatGregorian('en'));
    }

    public function testHijriInstancesShareADedicatedTranslatorPerLocale(): void
    {
        $first = Hijri::fromGregorian('2024-03-11')->locale('ar');
        $second = Hijri::fromGregorian('2020-10-16')->locale('ar');

        self::assertSame(
            spl_object_id($first->getLocalTranslator()),
            spl_object_id($second->getLocalTranslator())
        );
        self::assertNotSame(
            spl_object_id($this->getSharedTranslator('ar')),
            spl_object_id($first->getLocalTranslator())
        );
    }

    public function testLocaleVariantsKeepHijriMonthNamesAndLeaveGregorianDatesAlone(): void
    {
        $date = Hijri::fromGregorian('2024-03-11', null, -1);

        self::assertSame('1 رَمضان 1445', $date->copy()->locale('ar_EG')->isoFormat('D MMMM YYYY'));
        self::assertSame('1 رَمضان 1445', $date->copy()->locale('ar-SA')->isoFormat('D MMMM YYYY'));
        self::assertSame('1 Ramadan 1445', $date->copy()->locale('en_US')->isoFormat('D MMMM YYYY'));

        self::assertSame('11 مارس 2024', $this->formatGregorian('ar_EG'));
        self::assertSame('11 مارس 2024', $this->formatGregorian('ar-SA'));
        self::assertSame('11 March 2024', $this->formatGregorian('en_US'));
    }

    public function testFallbackLocalesAreStillUsed(): void
    {
        // Month names follow the requested locale, weekday names come from the fallback.
        $date = Hijri::fromGregorian('2024-03-11', null, -1)->locale('xx_unknown', 'ar');

        self::assertSame('الاثنين 1 Ramadan 1445', $date->isoFormat('dddd D MMMM YYYY'));
    }

    public function testCustomizedCarbonTranslationsStillApplyToHijriDates(): void
    {
        $shared = $this->getSharedTranslator('de_CH');
        $shared->setTranslations(['weekdays' => [
            'So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.',
        ]]);

        try {
            $hijri = Hijri::fromGregorian('2024-03-11', null, -1)->locale('de_CH');
            $gregorian = Carbon::parse('2024-03-11')->locale('de_CH');

            self::assertSame('Mo. 1 Ramadan 1445', $hijri->isoFormat('dddd D MMMM YYYY'));
            self::assertSame('Mo. 11 März 2024', $gregorian->isoFormat('dddd D MMMM YYYY'));
            self::assertSame($shared, $this->getSharedTranslator('de_CH'));
        } finally {
            $shared->resetMessages('de_CH');
        }
    }

    public function testTheSharedInstanceLocaleDoesNotLeakIntoConversions(): void
    {
        Hijri::getInstance()->locale('ar');

        self::assertSame('1 Ramadan 1445', Hijri::fromGregorian('2024-03-11', null, -1)->isoFormat('D MMMM YYYY'));
        self::assertSame('1 Ramadan 1445', Carbon::parse('2024-03-11')->toHijri()->isoFormat('D MMMM YYYY'));
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function safarTwentyNinthProvider(): array
    {
        return [
            'Safar 1442' => ['2020-10-16', '1442-02-29', '29 Safar 1442', '29 صفَر 1442'],
            'Safar 1443' => ['2021-10-06', '1443-02-29', '29 Safar 1443', '29 صفَر 1443'],
            'Safar 1445' => ['2023-09-14', '1445-02-29', '29 Safar 1445', '29 صفَر 1445'],
        ];
    }

    /**
     * @dataProvider safarTwentyNinthProvider
     */
    public function testTheTwentyNinthOfSafarIsKept(
        string $gregorian,
        string $hijri,
        string $english,
        string $arabic
    ): void {
        $date = Carbon::parse($gregorian)->toHijri(0);

        self::assertSame($hijri, $date->format('Y-m-d'));
        self::assertSame(2, $date->month);
        self::assertSame(29, $date->day);
        self::assertSame((int) substr($hijri, 0, 4), $date->year);
        self::assertSame($english, $date->locale('en')->isoFormat('D MMMM YYYY'));
        self::assertSame($arabic, $date->locale('ar')->isoFormat('D MMMM YYYY'));
    }

    public function testTheTwentyNinthOfSafarInEveryFormat(): void
    {
        $date = Hijri::fromGregorian('2020-10-16 13:05:09', 'UTC', 0)->locale('en');

        self::assertSame('Safar', $date->monthName);
        self::assertSame('29/02/1442 29 2 42 Safar Safar 29', $date->format('d/m/Y j n y F M t'));
        self::assertSame('Friday, 29 Safar 1442 13:05:09', $date->format('l, j F Y H:i:s'));
        self::assertSame('Y-m-d 1442-02-29', $date->format('\Y-\m-\d Y-m-d'));
        self::assertSame('1442-02-29T13:05:09+00:00', $date->format('c'));
        self::assertSame('Fri, 29 Safar 1442 13:05:09 +0000', $date->format('r'));
        self::assertSame('29th 58 1 5 5', $date->format('jS z L w N'));
        self::assertSame('29th 29 02 1442 42', $date->isoFormat('Do DD MM YYYY YY'));
        self::assertSame('Safar 29, 1442', $date->isoFormat('LL'));
        self::assertSame('Friday, Safar 29, 1442 1:05 PM', $date->isoFormat('LLLL'));
    }

    public function testEveryDayMatchesTheConverter(): void
    {
        $converter = new GregorianToHijriConverter();
        $day = new DateTimeImmutable('2000-01-01');
        $end = new DateTimeImmutable('2040-12-31');
        $oneDay = new DateInterval('P1D');
        $previous = null;

        while ($day <= $end) {
            $expected = $converter->convert($day, 0);
            $hijri = Hijri::fromGregorian($day, null, 0);
            $label = $day->format('Y-m-d');

            // The last day of each month and year must match format('t'), 'z' and 'L'.
            if ($previous !== null && $expected['day'] === 1) {
                self::assertSame($previous->format('j'), $previous->format('t'), $label);
            }

            if ($previous !== null && $expected['day'] === 1 && $expected['month'] === 1) {
                self::assertSame(
                    $previous->format('L') === '1' ? '354' : '353',
                    $previous->format('z'),
                    $label
                );
            }

            self::assertSame(
                [$expected['year'], $expected['month'], $expected['day']],
                [$hijri->year, $hijri->month, $hijri->day],
                $label
            );
            self::assertSame(
                sprintf('%04d-%02d-%02d', $expected['year'], $expected['month'], $expected['day']),
                $hijri->format('Y-m-d'),
                $label
            );

            $previous = $hijri;
            $day = $day->add($oneDay);
        }
    }

    public function testMutatedInstancesKeepTheirPreviousBehaviour(): void
    {
        $date = Hijri::fromGregorian('2024-03-11', null, -1)->addDay();

        self::assertSame(2, $date->day);
        self::assertSame('1445-09-02', $date->format('Y-m-d'));
        self::assertSame(Carbon::parse('1445-09-02')->dayOfWeek, $date->dayOfWeek);
    }

    public function testWeekdayPropertiesFollowTheOriginalDate(): void
    {
        $tuesday = Carbon::parse('2025-10-07')->toHijri(0);

        self::assertSame(2, $tuesday->dayOfWeek);
        self::assertSame(2, $tuesday->dayOfWeekIso);
        self::assertTrue($tuesday->isTuesday());
        self::assertTrue($tuesday->isWeekday());
        self::assertFalse($tuesday->isWeekend());
        self::assertSame('Tuesday', $tuesday->isoFormat('dddd'));

        $friday = Carbon::parse('2025-10-10')->toHijri(0);

        self::assertSame(5, $friday->dayOfWeek);
        self::assertSame(5, $friday->dayOfWeekIso);
        self::assertTrue($friday->isFriday());
        self::assertFalse($friday->isTuesday());

        $sunday = Carbon::parse('2025-10-12')->toHijri(0);

        self::assertSame(0, $sunday->dayOfWeek);
        self::assertSame(7, $sunday->dayOfWeekIso);
        self::assertTrue($sunday->isSunday());
        self::assertTrue($sunday->isWeekend());
    }

    public function testToGregorianReturnsTheOriginalDate(): void
    {
        $original = Carbon::parse('2024-03-11 20:15:30.123456', 'Asia/Riyadh');
        $gregorian = $original->toHijri()->toGregorian();

        self::assertSame(Carbon::class, get_class($gregorian));
        self::assertSame('2024-03-11 20:15:30.123456 Asia/Riyadh', $gregorian->format('Y-m-d H:i:s.u e'));
        self::assertTrue($gregorian->eq($original));
    }

    public function testToGregorianUsesTheAdjustmentOfTheConversion(): void
    {
        $date = Hijri::fromGregorian('2024-03-11', null, 1);

        self::assertSame('1445-09-03', $date->format('Y-m-d'));
        self::assertSame('2024-03-11', $date->toGregorian()->format('Y-m-d'));
        self::assertSame('2024-03-11', Carbon::parse('2024-03-11')->toHijri(-2)->toGregorian()->format('Y-m-d'));
    }

    public function testToGregorianKeepsTheMicrosecondsBeforeTheEpoch(): void
    {
        $date = Carbon::parse('1960-05-01 12:00:00.250000', 'UTC')->toHijri();

        self::assertSame('1960-05-01 12:00:00.250000', $date->toGregorian()->format('Y-m-d H:i:s.u'));
    }

    public function testToGregorianOfTheTwentyNinthOfSafar(): void
    {
        $date = Hijri::fromGregorian('2023-09-15');

        self::assertSame('1445-02-29', $date->format('Y-m-d'));
        self::assertSame('2023-09-15', $date->toGregorian()->format('Y-m-d'));
    }

    public function testToGregorianReturnsANewInstanceEachTime(): void
    {
        $date = Hijri::fromGregorian('2024-03-11');

        $date->toGregorian()->addYear();

        self::assertSame('2024-03-11', $date->toGregorian()->format('Y-m-d'));
        self::assertSame('1445-09-01', $date->format('Y-m-d'));
    }

    public function testToGregorianOfAChangedInstance(): void
    {
        $date = Hijri::fromGregorian('2024-03-11 08:00', 'Africa/Cairo', 1)->addDay()->setTime(21, 45);

        self::assertSame('1445-09-04', $date->format('Y-m-d'));
        self::assertSame(
            '2024-03-12 21:45:00 Africa/Cairo',
            $date->toGregorian()->format('Y-m-d H:i:s e')
        );
    }

    public function testToGregorianOfAChangedInstanceThatIsNotAHijriDate(): void
    {
        // Sha'aban 1445 has 29 days.
        $date = Hijri::fromGregorian('2024-02-11')->setDay(30);

        $this->expectException(InvalidHijriDateException::class);
        $this->expectExceptionMessage('Invalid Hijri date: 1445-08-30.');

        $date->toGregorian();
    }

    public function testToGregorianOfAnInstanceThatWasNeverConverted(): void
    {
        $date = Hijri::create(2024, 3, 11, 10, 30, 0, 'UTC');

        self::assertSame('2024-03-11 10:30:00 UTC', $date->toGregorian()->format('Y-m-d H:i:s e'));
    }

    public function testToGregorianRoundTripsEveryDay(): void
    {
        $oneDay = new DateInterval('P1D');

        foreach ([-2, -1, 0, 1, 2] as $adjustment) {
            $day = new DateTimeImmutable('2019-01-01 13:00:00');
            $end = new DateTimeImmutable('2026-12-31');

            while ($day <= $end) {
                self::assertSame(
                    $day->format('Y-m-d H:i:s'),
                    Hijri::fromGregorian($day, null, $adjustment)->toGregorian()->format('Y-m-d H:i:s'),
                    sprintf('%s with adjustment %d', $day->format('Y-m-d'), $adjustment)
                );

                $day = $day->add($oneDay);
            }
        }
    }

    /**
     * Format the same Gregorian date the bug reports use, optionally in a locale.
     */
    private function formatGregorian(?string $locale = null): string
    {
        $date = Carbon::parse('2024-03-11');

        if ($locale !== null) {
            $date->locale($locale);
        }

        return $date->isoFormat('D MMMM YYYY');
    }

    /**
     * Get Carbon's shared translator for a locale, as plain Carbon dates use it.
     */
    private function getSharedTranslator(string $locale): AbstractTranslator
    {
        $translator = Carbon::now()->locale($locale)->getLocalTranslator();

        self::assertInstanceOf(AbstractTranslator::class, $translator);

        return $translator;
    }
}
