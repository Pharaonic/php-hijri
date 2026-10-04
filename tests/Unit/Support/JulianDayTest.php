<?php

namespace Pharaonic\Hijri\Tests\Unit\Support;

use Pharaonic\Hijri\Support\JulianDay;
use PHPUnit\Framework\TestCase;

final class JulianDayTest extends TestCase
{
    public function testGregorianDateCanBeConvertedWithoutCalendarExtension(): void
    {
        self::assertSame(2449019, JulianDay::fromGregorian(1993, 1, 31));
    }
}
