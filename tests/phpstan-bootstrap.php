<?php

use Carbon\Carbon;
use Pharaonic\Hijri\Hijri;
use Pharaonic\Hijri\HijriCarbon;

// Register the Hijri mixin so Carbon's PHPStan extension can resolve its macros.
Carbon::mixin(HijriCarbon::class);

// Trait mixins are wrapped in instance closures; re-register the static
// factories as static closures so PHPStan treats them as static methods.
Carbon::macro(
    'fromHijri',
    static function (int $year, int $month, int $day, $tz = null, ?int $adjustment = null): Carbon {
        return Hijri::fromHijri($year, $month, $day, $tz, $adjustment);
    }
);

Carbon::macro(
    'parseHijri',
    static function (string $date, $tz = null, ?int $adjustment = null): Carbon {
        return Hijri::parseHijri($date, $tz, $adjustment);
    }
);
