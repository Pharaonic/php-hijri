<?php

namespace Pharaonic\Hijri;

use Carbon\Carbon;
use Carbon\AbstractTranslator;
use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;
use Pharaonic\Hijri\Converter\GregorianToHijriConverter;
use Pharaonic\Hijri\Support\HijriAdjustment;

class Hijri extends Carbon
{
    use HijriCarbon;

    /**
     * Hijri Months List (Arabic).
     *
     * @var array<int, string>
     */
    protected static $HIJRI_MONTHS = [
        'مُحرَّم',
        'صفَر',
        'ربيع الأول',
        'ربيع الآخر',
        'جمادى الأول',
        'جمادى الآخرة',
        'رَجب',
        'شَعبان',
        'رَمضان',
        'شوّال',
        'ذو القِعدة',
        'ذو الحِجّة',
    ];

    /**
     * Translated Hijri Months List (Not Arabic).
     *
     * @var array<int, string>
     */
    protected static $TRANS_HIJRI_MONTHS = [
        'Muharram',
        'Safar',
        'Rabi\' Al-Awwal',
        'Rabi\' Al-Akher',
        'Jumada Al-Awwal',
        'Jumada Al-Akherah',
        'Rajab',
        'Sha\'aban',
        'Ramadan',
        'Shawwal',
        'Dhu Al-Qi\'dah',
        'Dhu Al-Hijjah',
    ];

    /** @var Hijri|null */
    protected static $HIJRI_INSTANCE;

    /**
     * Dedicated translators holding the Hijri month names, keyed by locale.
     *
     * Carbon shares one translator per locale with every date in the process,
     * so the Hijri month names must never be written to those.
     *
     * @var array<string, AbstractTranslator>
     */
    protected static $HIJRI_TRANSLATORS = [];

    /** @var int|null */
    protected $CURRENT_DAY = null;

    /** @var int|null */
    protected $hijriYear = null;

    /** @var int|null */
    protected $hijriMonth = null;

    /** @var int|null */
    protected $hijriDay = null;

    /**
     * The underlying date and timezone right after the conversion, used to
     * detect whether the instance has been changed since.
     *
     * @var string|null
     */
    protected $hijriConvertedAt = null;

    /**
     * Get the shared Hijri entry-point used by the legacy API.
     */
    public static function getInstance(): Hijri
    {
        return self::$HIJRI_INSTANCE ?? self::$HIJRI_INSTANCE = new self();
    }

    /**
     * Prepare a Hijri instance from a Gregorian Carbon-compatible date.
     *
     * @template T of Hijri
     * @param T $obj
     * @return T
     */
    public function prepare(Hijri $obj, ?int $adjustment = null): Hijri
    {
        $obj->hijriConvertedAt = null;
        $obj->locale(static::getLocale());
        $obj->CURRENT_DAY = $obj->dayOfWeek;

        return $obj->convertToHijri($adjustment ?? HijriAdjustment::get());
    }

    /**
     * Convert the current Gregorian values to Hijri values.
     */
    private function convertToHijri(int $adjustment): static
    {
        $components = (new GregorianToHijriConverter())->convert(
            $this,
            $adjustment
        );

        $this->hijriYear = $components['year'];
        $this->hijriMonth = $components['month'];
        $this->hijriDay = $components['day'];

        // The Hijri values are also stored as a Gregorian date for Carbon. A
        // day that doesn't exist in that Gregorian month (29 Safar of a common
        // year) overflows, so the values above stay the source of truth.
        $this->setDate(
            $components['year'],
            $components['month'],
            $components['day']
        );

        $this->hijriConvertedAt = $this->getHijriState();

        return $this;
    }

    /**
     * Get the underlying date and timezone, as stored by Carbon.
     */
    private function getHijriState(): string
    {
        return $this->rawFormat('Y-m-d H:i:s.u e');
    }

    /**
     * Determine if the instance still holds the date it was converted to.
     *
     * Once it's changed (addDays(), setDate(), setTimezone()...) the Hijri
     * values no longer apply and Carbon's own values are used.
     */
    protected function isUnchangedHijri(): bool
    {
        return $this->hijriConvertedAt !== null
            && $this->hijriConvertedAt === $this->getHijriState();
    }

    /**
     * Get a part of the instance.
     *
     * The year, month, day and weekday are the Hijri ones while the instance
     * is unchanged since its conversion.
     *
     * @param mixed $name
     */
    public function get($name): mixed
    {
        if (is_string($name) && $this->isUnchangedHijri()) {
            switch ($name) {
                case 'year':
                    return $this->hijriYear;
                case 'month':
                    return $this->hijriMonth;
                case 'day':
                    return $this->hijriDay;
                case 'dayOfWeek':
                    return $this->CURRENT_DAY;
                case 'dayOfWeekIso':
                    return $this->CURRENT_DAY === 0 ? 7 : $this->CURRENT_DAY;
            }
        }

        return parent::get($name);
    }

    /**
     * Get the units used by isoFormat(), reading the day, month and year
     * through the Hijri values instead of the stored Gregorian date.
     *
     * @return array<string, mixed>
     */
    public static function getIsoUnits(): array
    {
        $units = parent::getIsoUnits();

        $units['DD'] = ['getPaddedUnit', ['day']];
        $units['MM'] = ['getPaddedUnit', ['month']];
        $units['YY'] = static function (CarbonInterface $date): string {
            return sprintf('%02d', $date->year % 100);
        };

        return $units;
    }

    /**
     * Convert a Gregorian Carbon-supported input to Hijri using an optional
     * per-call adjustment without changing the global adjustment.
     *
     * @param string|DateTimeInterface|null $time
     * @param DateTimeZone|string|null $tz
     */
    public static function fromGregorian($time = null, $tz = null, ?int $adjustment = null): static
    {
        $instance = self::getInstance();

        /** @var static $parsed */
        $parsed = parent::parse($time, $tz);

        return $instance->prepare($parsed, $adjustment);
    }

    /**
     * Create a Hijri instance from a Carbon-supported input.
     *
     * @param string|DateTimeInterface|null $time
     * @param DateTimeZone|string|null $tz
     */
    public static function parse($time = null, $tz = null): static
    {
        return self::fromGregorian($time, $tz);
    }

    /**
     * Get/set the locale for the current instance.
     *
     * @return $this|string
     */
    public function locale(?string $locale = null, ...$fallbackLocales): static|string
    {
        if ($locale === null) {
            return $this->getTranslatorLocale();
        }

        parent::locale($locale, ...$fallbackLocales);

        $translator = $this->getLocalTranslator();

        if ($translator instanceof AbstractTranslator) {
            $this->setLocalTranslator(self::getHijriTranslator($translator));
        }

        return $this;
    }

    /**
     * Get the dedicated Hijri translator matching the given Carbon translator.
     *
     * It's built once per locale as a copy of the Carbon translator (so
     * customised weekday names, formats and fallback locales still apply),
     * with the Hijri month names on top. A copy is used rather than a new
     * translator: creating one drops Carbon's shared translator for that
     * locale, and its customisations with it.
     */
    protected static function getHijriTranslator(AbstractTranslator $translator): AbstractTranslator
    {
        if (in_array($translator, self::$HIJRI_TRANSLATORS, true)) {
            return $translator;
        }

        $locale = $translator->getLocale();
        $key = $locale . '|' . implode(',', $translator->getFallbackLocales());

        if (! isset(self::$HIJRI_TRANSLATORS[$key])) {
            $hijriTranslator = clone $translator;
            $months = substr($locale, 0, 2) === 'ar' ? self::$HIJRI_MONTHS : self::$TRANS_HIJRI_MONTHS;

            $hijriTranslator->setMessages($locale, [
                'months' => $months,
                'months_short' => $months,
            ]);

            self::$HIJRI_TRANSLATORS[$key] = $hijriTranslator;
        }

        return self::$HIJRI_TRANSLATORS[$key];
    }

    public function getTranslatedDayName($context = null, $keySuffix = '', $defaultValue = null): string
    {
        return $this->getTranslatedFormByRegExp(
            'weekdays',
            $keySuffix,
            $context,
            $this->CURRENT_DAY,
            $defaultValue ?: $this->englishDayOfWeek
        );
    }

    /**
     * Format the instance with Hijri values.
     *
     * While the instance is unchanged since its conversion, the date
     * characters read the Hijri values: d, j, m, n, Y, y, t (days in the Hijri
     * month), z (day of the Hijri year, from 0), L (Hijri leap year), S
     * (English suffix of the Hijri day), w and N (original weekday), and c and
     * r (built from the Hijri date). l, D, F and M print the localized weekday
     * and Hijri month names. Other characters are formatted by Carbon.
     */
    public function format($format): string
    {
        if ($this->isUnchangedHijri()) {
            return parent::format($this->toHijriFormat((string) $format));
        }

        return str_replace(
            [
                $this->englishDayOfWeek,
                $this->englishMonth,
                $this->shortEnglishDayOfWeek,
                $this->shortEnglishMonth,
            ],
            [
                $this->dayName,
                $this->monthName,
                $this->shortDayName,
                $this->monthName,
            ],
            parent::format($format)
        );
    }

    /**
     * Replace the date characters of a format() string with escaped Hijri
     * values, keeping escaped characters as they are.
     */
    private function toHijriFormat(string $format): string
    {
        $result = '';
        $length = strlen($format);

        for ($i = 0; $i < $length; $i++) {
            $char = $format[$i];

            if ($char === '\\') {
                $result .= substr($format, $i++, 2);

                continue;
            }

            if ($char === 'c') {
                $result .= $this->toHijriFormat('Y-m-d\TH:i:sP');

                continue;
            }

            if ($char === 'r') {
                $result .= $this->toHijriFormat('D, d M Y H:i:s O');

                continue;
            }

            $value = $this->getHijriFormatValue($char);

            $result .= $value === null ? $char : preg_replace('/./su', '\\\\$0', $value);
        }

        return $result;
    }

    /**
     * Get the Hijri value of a format() character, or null if Carbon formats it.
     */
    private function getHijriFormatValue(string $char): ?string
    {
        $year = (int) $this->hijriYear;
        $month = (int) $this->hijriMonth;
        $day = (int) $this->hijriDay;
        $converter = new GregorianToHijriConverter();

        switch ($char) {
            case 'd':
                return sprintf('%02d', $day);
            case 'j':
                return (string) $day;
            case 'S':
                return $this->getEnglishOrdinalSuffix($day);
            case 'z':
                $dayOfYear = $day - 1;

                for ($previous = 1; $previous < $month; $previous++) {
                    $dayOfYear += $converter->daysInMonth($year, $previous);
                }

                return (string) $dayOfYear;
            case 'w':
                return (string) $this->CURRENT_DAY;
            case 'N':
                return (string) ($this->CURRENT_DAY === 0 ? 7 : $this->CURRENT_DAY);
            case 'l':
                return $this->dayName;
            case 'D':
                return $this->shortDayName;
            case 'm':
                return sprintf('%02d', $month);
            case 'n':
                return (string) $month;
            case 'F':
            case 'M':
                return $this->monthName;
            case 't':
                return (string) $converter->daysInMonth($year, $month);
            case 'L':
                return $converter->daysInYear($year) === 355 ? '1' : '0';
            case 'Y':
                return sprintf('%04d', $year);
            case 'y':
                return sprintf('%02d', $year % 100);
        }

        return null;
    }

    /**
     * Get the English ordinal suffix of a day number (st, nd, rd, th).
     */
    private function getEnglishOrdinalSuffix(int $day): string
    {
        if ($day % 100 >= 11 && $day % 100 <= 13) {
            return 'th';
        }

        switch ($day % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
        }

        return 'th';
    }
}
