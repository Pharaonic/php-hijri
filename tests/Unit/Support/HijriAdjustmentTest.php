<?php

namespace Pharaonic\Hijri\Tests\Unit\Support;

use Pharaonic\Hijri\Support\HijriAdjustment;
use PHPUnit\Framework\TestCase;

final class HijriAdjustmentTest extends TestCase
{
    protected function tearDown(): void
    {
        HijriAdjustment::set(-1);

        parent::tearDown();
    }

    public function testAdjustmentStateIsShared(): void
    {
        HijriAdjustment::set(2);

        self::assertSame(2, HijriAdjustment::get());
    }
}
