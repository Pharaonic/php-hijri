<?php

namespace Pharaonic\Hijri\Tests;

use Carbon\Carbon;
use Pharaonic\Hijri\Calendar\HijriCalendar;
use Pharaonic\Hijri\Exception\InvalidHijriDateException;
use Pharaonic\Hijri\Hijri;
use Pharaonic\Hijri\HijriCarbon;
use PHPUnit\Framework\TestCase;

final class HijriTest extends TestCase
{
    /** @var Carbon */
    private $date;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::mixin(HijriCarbon::class);
        Hijri::getInstance()->setHijriAdjustment(-1);

        $this->date = Carbon::parse('01-02-1993 19:00:00');
    }

    public function testLegacyCarbonMixinApiRemainsCompatible(): void
    {
        self::assertSame(
            'Monday, Sha\'aban 8, 1413 7:00 PM',
            $this->date->toHijri()->isoFormat('LLLL')
        );
    }

    public function testArabicLocaleRemainsCompatible(): void
    {
        self::assertSame(
            'الاثنين 8 شَعبان 1413 19:00',
            $this->date->toHijri()->locale('ar')->isoFormat('LLLL')
        );
    }

    public function testEnglishHijriMonthNames(): void
    {
        $date = Hijri::fromGregorian('2024-03-11', null, -1);

        self::assertSame('1 Ramadan 1445', $date->locale('en')->isoFormat('D MMMM YYYY'));
    }

    public function testArabicHijriMonthNames(): void
    {
        $date = Hijri::fromGregorian('2024-03-11', null, -1);

        self::assertSame('1 رَمضان 1445', $date->locale('ar')->isoFormat('D MMMM YYYY'));
    }

    public function testDirectParseIsSafeWithoutCallingGetInstanceFirst(): void
    {
        $date = Hijri::parse('01-02-1993 19:00:00');

        self::assertSame('1413-08-08', $date->format('Y-m-d'));
    }

    public function testPerCallAdjustmentDoesNotChangeGlobalAdjustment(): void
    {
        self::assertSame(-1, Hijri::getInstance()->getHijriAdjustment());

        $date = $this->date->copy()->toHijri(0);

        self::assertSame(-1, Hijri::getInstance()->getHijriAdjustment());
        self::assertNotSame('1413-08-08', $date->format('Y-m-d'));
    }

    public function testCanCreateGregorianCarbonFromHijriComponents(): void
    {
        $date = Carbon::fromHijri(1413, 8, 8);

        self::assertSame('1993-02-01', $date->format('Y-m-d'));
    }

    public function testCanParseHijriDateWithTime(): void
    {
        $date = Carbon::parseHijri('1413-08-08 19:30:45');

        self::assertSame('1993-02-01 19:30:45', $date->format('Y-m-d H:i:s'));
    }

    public function testHijriCalendarValidation(): void
    {
        self::assertTrue(HijriCalendar::isValidDate(1445, 9, 1));
        self::assertFalse(HijriCalendar::isValidDate(1445, 13, 1));
        self::assertSame(30, HijriCalendar::daysInMonth(1445, 9));
    }

    public function testInvalidHijriDateThrowsException(): void
    {
        $this->expectException(InvalidHijriDateException::class);

        Carbon::fromHijri(1445, 13, 1);
    }
}
