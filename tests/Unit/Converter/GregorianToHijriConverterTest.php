<?php

namespace Pharaonic\Hijri\Tests\Unit\Converter;

use DateTimeImmutable;
use Pharaonic\Hijri\Converter\GregorianToHijriConverter;
use PHPUnit\Framework\TestCase;

final class GregorianToHijriConverterTest extends TestCase
{
    public function testItPreservesTheLegacyConversionResult(): void
    {
        $result = (new GregorianToHijriConverter())->convert(
            new DateTimeImmutable('1993-02-01 19:00:00'),
            -1
        );

        self::assertSame([
            'year' => 1413,
            'month' => 8,
            'day' => 8,
        ], $result);
    }

    public function testAdjustmentIsExplicitInTheConverter(): void
    {
        $converter = new GregorianToHijriConverter();
        $date = new DateTimeImmutable('1993-02-01');

        self::assertNotSame(
            $converter->convert($date, -1),
            $converter->convert($date, 0)
        );
    }
}
