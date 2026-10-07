<?php

namespace Pharaonic\Hijri\Concerns;

/**
 * Hijri differences for Carbon 2.
 */
trait HijriDifference
{
    /**
     * Get the difference in whole Hijri months.
     *
     * @param mixed $date
     * @param bool $absolute
     * @return int
     */
    public function diffInMonths($date = null, $absolute = true)
    {
        $date = $this->resolveCarbon($date)->copy()->setTimezone($this->getTimezone());

        [$yearStart, $monthStart, $dayStart] = explode('-', $this->format('Y-m-dHisu'));
        [$yearEnd, $monthEnd, $dayEnd] = explode('-', $date->format('Y-m-dHisu'));

        $diff = (((int) $yearEnd - (int) $yearStart) * 12) + (int) $monthEnd - (int) $monthStart;

        if ($diff > 0) {
            $diff -= $dayStart > $dayEnd ? 1 : 0;
        } elseif ($diff < 0) {
            $diff += $dayStart < $dayEnd ? 1 : 0;
        }

        return $absolute ? abs($diff) : $diff;
    }

    /**
     * Get the difference in whole Hijri years.
     *
     * @param mixed $date
     * @param bool $absolute
     * @return int
     */
    public function diffInYears($date = null, $absolute = true)
    {
        return intdiv($this->diffInMonths($date, $absolute), 12);
    }
}
