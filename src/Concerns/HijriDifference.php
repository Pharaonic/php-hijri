<?php

// The Hijri months and years differences return an int in Carbon 2 and a
// float in Carbon 3, so the Pharaonic\Hijri\Concerns\HijriDifference trait is
// loaded from the file matching the installed version.

use Carbon\Carbon;

if ((new ReflectionMethod(Carbon::class, 'diffInYears'))->hasReturnType()) {
    require __DIR__ . '/Carbon3/HijriDifference.php';
} else {
    require __DIR__ . '/Carbon2/HijriDifference.php';
}
