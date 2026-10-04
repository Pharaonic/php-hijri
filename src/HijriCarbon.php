<?php

namespace Pharaonic\Hijri;

use Carbon\Carbon;
use DateTimeZone;
use Pharaonic\Hijri\Converter\HijriToGregorianConverter;
use Pharaonic\Hijri\Exception\InvalidHijriDateException;
use Pharaonic\Hijri\Support\HijriAdjustment;

trait HijriCarbon
{
    /**
     * Set the default Hijri adjustment in days.
     *
     * Kept for backwards compatibility with the existing Carbon mixin API.
     */
    public function setHijriAdjustment(int $days): void
    {
        HijriAdjustment::set($days);
    }

    /**
     * Get the default Hijri adjustment in days.
     */
    public function getHijriAdjustment(): int
    {
        return HijriAdjustment::get();
    }

    /**
     * Convert the current Gregorian Carbon date to Hijri.
     *
     * Passing an adjustment affects this conversion only and does not mutate
     * the package-wide default adjustment.
     */
    public function toHijri(?int $adjustment = null): Hijri
    {
        return Hijri::fromGregorian($this, null, $adjustment);
    }

    /**
     * Create a Gregorian Carbon date from Hijri components.
     *
     * @param DateTimeZone|string|null $tz
     */
    public static function fromHijri(
        int $year,
        int $month,
        int $day,
        $tz = null,
        ?int $adjustment = null
    ): Carbon {
        $components = (new HijriToGregorianConverter())->convert(
            $year,
            $month,
            $day,
            $adjustment ?? HijriAdjustment::get()
        );

        return Carbon::create(
            $components['year'],
            $components['month'],
            $components['day'],
            0,
            0,
            0,
            $tz
        );
    }

    /**
     * Parse a Hijri date string and return its Gregorian Carbon equivalent.
     *
     * Supported format: YYYY-MM-DD with an optional HH:MM[:SS] time part.
     *
     * @param DateTimeZone|string|null $tz
     */
    public static function parseHijri(
        string $date,
        $tz = null,
        ?int $adjustment = null
    ): Carbon {
        $pattern = '/^\s*(\d{1,6})-(\d{1,2})-(\d{1,2})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?\s*$/' ;

        if (! preg_match($pattern, $date, $matches)) {
            throw new InvalidHijriDateException(
                'Hijri date must use YYYY-MM-DD with an optional HH:MM[:SS] time part.'
            );
        }

        $carbon = self::fromHijri(
            (int) $matches[1],
            (int) $matches[2],
            (int) $matches[3],
            $tz,
            $adjustment
        );

        if (isset($matches[4]) && $matches[4] !== '') {
            $carbon->setTime(
                (int) $matches[4],
                (int) $matches[5],
                isset($matches[6]) && $matches[6] !== '' ? (int) $matches[6] : 0
            );
        }

        return $carbon;
    }
}
