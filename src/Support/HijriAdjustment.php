<?php

namespace Pharaonic\Hijri\Support;

final class HijriAdjustment
{
    /** @var int */
    private static $days = -1;

    public static function set(int $days): void
    {
        self::$days = $days;
    }

    public static function get(): int
    {
        return self::$days;
    }
}
