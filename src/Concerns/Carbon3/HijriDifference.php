<?php

namespace Pharaonic\Hijri\Concerns;

/**
 * Hijri differences for Carbon 3.
 */
trait HijriDifference
{
    /**
     * Get the difference in Hijri years, with the fraction of the
     * current year.
     *
     * @param mixed $date
     * @param bool $absolute
     * @param bool $utc
     */
    public function diffInYears($date = null, $absolute = false, $utc = false): float
    {
        $start = $this;
        $end = $this->resolveCarbon($date);

        if ($utc) {
            $start = $start->avoidMutation()->utc();
            $end = $end->avoidMutation()->utc();
        }

        $ascending = ($start <= $end);
        $sign = $absolute || $ascending ? 1 : -1;

        if (! $ascending) {
            [$start, $end] = [$end, $start];
        }

        $years = intdiv((int) $start->diffInMonths($end, true), 12);
        $floorEnd = $start->avoidMutation()->addYears($years);

        if ($floorEnd >= $end) {
            return $sign * $years;
        }

        $ceilEnd = $start->avoidMutation()->addYears($years + 1);
        $daysToFloor = $floorEnd->diffInDays($end);
        $daysToCeil = $end->diffInDays($ceilEnd);

        return $sign * ($years + $daysToFloor / ($daysToCeil + $daysToFloor));
    }
}
