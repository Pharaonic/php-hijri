<?php

namespace Pharaonic\Hijri;

use Carbon\AbstractTranslator;
use Carbon\Carbon;
use Carbon\CarbonConverterInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use Pharaonic\Hijri\Calendar\HijriCalendar;
use Pharaonic\Hijri\Concerns\HijriDifference;
use Pharaonic\Hijri\Converter\GregorianToHijriConverter;
use Pharaonic\Hijri\Converter\HijriToGregorianConverter;
use Pharaonic\Hijri\Support\HijriAdjustment;

/**
 * A Carbon date read in the Hijri calendar.
 *
 * The instance holds the real (Gregorian) date and time, so timestamps,
 * comparisons and differences work with any other date. The year, month and
 * day are read, set and changed in the Hijri calendar.
 */
class Hijri extends Carbon
{
    use HijriCarbon;
    use HijriDifference;

    /**
     * Hijri Months List (Arabic).
     *
     * @var array<int, string>
     */
    protected static $HIJRI_MONTHS = [
        'مُحرَّم',
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

    /**
     * The adjustment of the instance in days, or null to follow the global
     * adjustment.
     *
     * @var int|null
     */
    protected $hijriAdjustment = null;

    /**
     * The last converted Hijri date, keyed by the Gregorian date and the
     * adjustment it was converted from.
     *
     * @var array{key: string, year: int, month: int, day: int}|null
     */
    private $hijriDate = null;

    /**
     * Get the shared Hijri entry-point used by the legacy API.
     */
    public static function getInstance(): Hijri
    {
        return self::$HIJRI_INSTANCE ?? self::$HIJRI_INSTANCE = new self();
    }

    /**
     * Prepare a Hijri instance with the given adjustment (the global one by
     * default) and the current locale.
     *
     * @template T of Hijri
     * @param T $obj
     * @return T
     */
    public function prepare(Hijri $obj, ?int $adjustment = null): Hijri
    {
        $obj->hijriAdjustment = $adjustment ?? HijriAdjustment::get();
        $obj->locale(static::getLocale());

        return $obj;
    }

    /**
     * Convert a Gregorian Carbon-supported input to Hijri using an optional
     * per-call adjustment without changing the global adjustment.
     *
     * A DateTimeInterface keeps its exact instant and timezone (unless $tz is
     * given, in which case the same instant is moved to $tz).
     *
     * @param string|DateTimeInterface|null $time
     * @param DateTimeZone|string|null $tz
     */
    public static function fromGregorian($time = null, $tz = null, ?int $adjustment = null): static
    {
        if ($time instanceof DateTimeInterface) {
            /** @var static $parsed */
            $parsed = static::createFromFormat('U.u', $time->format('U.u'));
            $parsed->setTimezone($tz ?? $time->getTimezone());
        } else {
            /** @var static $parsed */
            $parsed = parent::parse($time, $tz);
        }

        return self::getInstance()->prepare($parsed, $adjustment);
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
     * Get the Gregorian date of the instance: the same date, time and
     * timezone, as a Carbon instance.
     */
    public function toGregorian(): Carbon
    {
        /** @var Carbon $gregorian */
        $gregorian = Carbon::createFromFormat(
            'U.u',
            sprintf('%d.%06d', $this->getTimestamp(), $this->micro)
        );

        return $gregorian->setTimezone($this->getTimezone());
    }

    /**
     * Get the Gregorian date of the instance as a CarbonImmutable.
     */
    public function toImmutable(): CarbonImmutable
    {
        return CarbonImmutable::instance($this->toGregorian());
    }

    /**
     * Get the Hijri year, month and day of the instance, in its timezone.
     *
     * @return array{year: int, month: int, day: int}
     */
    protected function getHijriDate(): array
    {
        $adjustment = $this->getConversionAdjustment();
        $key = $this->rawFormat('Y-m-d') . '|' . $adjustment;

        if ($this->hijriDate === null || $this->hijriDate['key'] !== $key) {
            $this->hijriDate = ['key' => $key] + (new GregorianToHijriConverter())->convert($this, $adjustment);
        }

        return [
            'year' => $this->hijriDate['year'],
            'month' => $this->hijriDate['month'],
            'day' => $this->hijriDate['day'],
        ];
    }

    /**
     * Get the adjustment the instance is converted with.
     */
    protected function getConversionAdjustment(): int
    {
        return $this->hijriAdjustment ?? HijriAdjustment::get();
    }

    /**
     * Get a part of the instance.
     *
     * The year, month, day and the values built from them (dayOfYear,
     * daysInMonth, quarter...) are Hijri ones.
     *
     * @param mixed $name
     */
    public function get($name): mixed
    {
        $name = self::enumValue($name);

        switch ($name) {
            case 'year':
            case 'month':
            case 'day':
                return $this->getHijriDate()[$name];
            case 'daysInMonth':
                $date = $this->getHijriDate();

                return HijriCalendar::daysInMonth($date['year'], $date['month']);
            case 'dayOfYear':
                return (int) $this->getHijriFormatValue('z') + 1;
            case 'daysInYear':
                return $this->isLeapYear() ? 355 : 354;
            case 'englishMonth':
            case 'shortEnglishMonth':
                return self::$TRANS_HIJRI_MONTHS[$this->getHijriDate()['month'] - 1];
        }

        return parent::get($name);
    }

    /**
     * Set a part of the instance. The year, month and day are Hijri ones.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function set($name, $value = null): static
    {
        if (is_array($name)) {
            foreach ($name as $key => $part) {
                $this->set($key, $part);
            }

            return $this;
        }

        $unit = self::enumValue($name);

        if (in_array($unit, ['year', 'month', 'day'], true)) {
            $date = $this->getHijriDate();
            $date[$unit] = (int) self::enumValue($value);
            $this->setDate($date['year'], $date['month'], $date['day']);

            return $this;
        }

        return parent::set($name, $value);
    }

    /**
     * Set the Hijri date, keeping the time.
     *
     * Like DateTime::setDate(), a month or day out of range moves the date
     * forward or backward: setDate(1445, 2, 30) is the 1st of Rabi' Al-Awwal
     * 1445, as Safar 1445 has 29 days.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function setDate($year, $month, $day): static
    {
        $months = ((int) $year * 12) + (int) $month - 1;
        $year = (int) floor($months / 12);
        $month = $months - ($year * 12) + 1;

        $first = (new HijriToGregorianConverter())->convert(
            $year,
            $month,
            1,
            $this->getConversionAdjustment()
        );

        parent::setDate($first['year'], $first['month'], $first['day'] + (int) $day - 1);

        return $this;
    }

    /**
     * Add an amount of a unit. Months, quarters, years, decades, centuries
     * and millennia are Hijri ones; other units are passed to Carbon.
     *
     * @param mixed $unit
     * @param int|float $value
     * @param mixed $overflow
     * @param int|null $anchorDay
     */
    public function addUnit($unit, $value = 1, $overflow = null, $anchorDay = null): static
    {
        $units = [
            'month' => [1, 'month'],
            'quarter' => [static::MONTHS_PER_QUARTER, 'month'],
            'year' => [1, 'year'],
            'decade' => [static::YEARS_PER_DECADE, 'year'],
            'century' => [static::YEARS_PER_CENTURY, 'year'],
            'millennium' => [static::YEARS_PER_MILLENNIUM, 'year'],
        ];
        $name = static::singularUnit((string) self::enumValue($unit));

        if (! isset($units[$name]) || ! is_numeric($value)) {
            return parent::addUnit(...func_get_args());
        }

        [$factor, $baseUnit] = $units[$name];

        // Like Carbon, only whole months or years are added.
        $months = (int) ($value * $factor) * ($baseUnit === 'year' ? 12 : 1);
        $mode = is_object($overflow) ? $overflow->name : $overflow;

        if ($anchorDay !== null || $mode === 'AnchorDay') {
            $this->addHijriMonths($months, false, $anchorDay ?? $this->getHijriDate()['day']);
        } else {
            $this->addHijriMonths($months, $this->shouldOverflowHijri($baseUnit, $mode), null);
        }

        return $this;
    }

    /**
     * Add a DateInterval. Its years and months are Hijri ones.
     */
    public function rawAdd(DateInterval $interval): static
    {
        return $this->addHijriInterval($interval, 1);
    }

    /**
     * Subtract a DateInterval. Its years and months are Hijri ones.
     */
    public function rawSub(DateInterval $interval): static
    {
        return $this->addHijriInterval($interval, -1);
    }

    /**
     * Add an interval, or an amount of a unit.
     *
     * @param mixed $unit
     * @param mixed $value
     * @param mixed $overflow
     * @param int|null $anchorDay
     */
    public function add($unit, $value = 1, $overflow = null, $anchorDay = null): static
    {
        if ($unit instanceof DateInterval && ! $unit instanceof CarbonConverterInterface) {
            return $this->rawAdd($unit);
        }

        return parent::add(...func_get_args());
    }

    /**
     * Subtract an interval, or an amount of a unit.
     *
     * @param mixed $unit
     * @param mixed $value
     * @param mixed $overflow
     * @param int|null $anchorDay
     */
    public function sub($unit, $value = 1, $overflow = null, $anchorDay = null): static
    {
        if ($unit instanceof DateInterval && ! $unit instanceof CarbonConverterInterface) {
            return $this->rawSub($unit);
        }

        return parent::sub(...func_get_args());
    }

    /**
     * Add Hijri months, keeping the day when it exists in the target month.
     */
    private function addHijriMonths(int $months, bool $overflow, ?int $anchorDay): void
    {
        $date = $this->getHijriDate();
        $months += ($date['year'] * 12) + $date['month'] - 1;
        $year = (int) floor($months / 12);
        $month = $months - ($year * 12) + 1;
        $day = $anchorDay ?? $date['day'];

        if (! $overflow || $anchorDay !== null) {
            $day = min($day, HijriCalendar::daysInMonth($year, $month));
        }

        $this->setDate($year, $month, $day);
    }

    /**
     * Add the years and months of an interval as Hijri ones, then the rest.
     */
    private function addHijriInterval(DateInterval $interval, int $sign): static
    {
        if ($interval->invert) {
            $sign = -$sign;
        }

        $months = (($interval->y * 12) + $interval->m) * $sign;

        if ($months !== 0) {
            $this->addHijriMonths($months, true, null);
        }

        $rest = new DateInterval('PT0S');
        $rest->d = $interval->d;
        $rest->h = $interval->h;
        $rest->i = $interval->i;
        $rest->s = $interval->s;
        $rest->f = $interval->f;
        $rest->invert = $sign < 0 ? 1 : 0;

        parent::rawAdd($rest);

        return $this;
    }

    /**
     * Determine if adding months or years can overflow into the next month.
     *
     * @param bool|string|null $mode
     */
    private function shouldOverflowHijri(string $unit, $mode): bool
    {
        if ($mode !== null) {
            return $mode === true || $mode === 'Overflow';
        }

        $ucUnit = ucfirst($unit) . 's';

        return $this->{'local' . $ucUnit . 'Overflow'} ?? static::{'shouldOverflow' . $ucUnit}();
    }

    /**
     * Get the value of a backed enum (Carbon 3's Unit, Month...), or the
     * value itself.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function enumValue($value)
    {
        return is_object($value) && property_exists($value, 'value') ? $value->value : $value;
    }

    /**
     * Go to the end of the Hijri year.
     */
    public function endOfYear(): static
    {
        return $this->setDate($this->year, 12, 1)->endOfMonth();
    }

    /**
     * Go to the end of the Hijri decade.
     */
    public function endOfDecade(): static
    {
        $year = $this->year - $this->year % static::YEARS_PER_DECADE + static::YEARS_PER_DECADE - 1;

        return $this->setDate($year, 12, 1)->endOfMonth();
    }

    /**
     * Go to the end of the Hijri century.
     */
    public function endOfCentury(): static
    {
        $year = $this->year - 1 - ($this->year - 1) % static::YEARS_PER_CENTURY + static::YEARS_PER_CENTURY;

        return $this->setDate($year, 12, 1)->endOfMonth();
    }

    /**
     * Go to the end of the Hijri millennium.
     */
    public function endOfMillennium(): static
    {
        $year = $this->year - 1 - ($this->year - 1) % static::YEARS_PER_MILLENNIUM + static::YEARS_PER_MILLENNIUM;

        return $this->setDate($year, 12, 1)->endOfMonth();
    }

    /**
     * Go to the first day of the Hijri month, or to its first given weekday.
     *
     * @param int|null $dayOfWeek
     */
    public function firstOfMonth($dayOfWeek = null): static
    {
        $this->startOfDay()->day(1);

        if ($dayOfWeek !== null && $this->dayOfWeek !== (int) $dayOfWeek) {
            $this->next((int) $dayOfWeek);
        }

        return $this;
    }

    /**
     * Go to the last day of the Hijri month, or to its last given weekday.
     *
     * @param int|null $dayOfWeek
     */
    public function lastOfMonth($dayOfWeek = null): static
    {
        $this->startOfDay()->day($this->daysInMonth);

        if ($dayOfWeek !== null && $this->dayOfWeek !== (int) $dayOfWeek) {
            $this->previous((int) $dayOfWeek);
        }

        return $this;
    }

    /**
     * Go to the given occurrence of a weekday in the Hijri month, or return
     * false if the month doesn't have it.
     *
     * @param int $nth
     * @param int $dayOfWeek
     * @return static|false
     */
    public function nthOfMonth($nth, $dayOfWeek)
    {
        $date = $this->avoidMutation()->firstOfMonth()->modify('+' . $nth . ' ' . static::$days[$dayOfWeek]);

        return $date->year === $this->year && $date->month === $this->month
            ? $this->modify($date->rawFormat('Y-m-d H:i:s.u'))
            : false;
    }

    /**
     * Go to the given occurrence of a weekday in the Hijri quarter, or return
     * false if the quarter doesn't have it.
     *
     * @param int $nth
     * @param int $dayOfWeek
     * @return static|false
     */
    public function nthOfQuarter($nth, $dayOfWeek)
    {
        $date = $this->avoidMutation()->firstOfQuarter()->modify('+' . $nth . ' ' . static::$days[$dayOfWeek]);

        return $date->year === $this->year && $date->quarter === $this->quarter
            ? $this->modify($date->rawFormat('Y-m-d H:i:s.u'))
            : false;
    }

    /**
     * Go to the given occurrence of a weekday in the Hijri year, or return
     * false if the year doesn't have it.
     *
     * @param int $nth
     * @param int $dayOfWeek
     * @return static|false
     */
    public function nthOfYear($nth, $dayOfWeek)
    {
        $date = $this->avoidMutation()->firstOfYear()->modify('+' . $nth . ' ' . static::$days[$dayOfWeek]);

        return $date->year === $this->year
            ? $this->modify($date->rawFormat('Y-m-d H:i:s.u'))
            : false;
    }

    /**
     * Determine if the instance is today.
     */
    public function isToday(): bool
    {
        return $this->rawFormat('Y-m-d') === $this->nowWithSameTz()->rawFormat('Y-m-d');
    }

    /**
     * Determine if the instance is yesterday.
     */
    public function isYesterday(): bool
    {
        return $this->rawFormat('Y-m-d') === static::yesterday($this->getTimezone())->rawFormat('Y-m-d');
    }

    /**
     * Determine if the instance is tomorrow.
     */
    public function isTomorrow(): bool
    {
        return $this->rawFormat('Y-m-d') === static::tomorrow($this->getTimezone())->rawFormat('Y-m-d');
    }

    /**
     * Determine if the given date (today by default) has the same Hijri month
     * and day as the instance.
     *
     * @param DateTimeInterface|string|null $date
     */
    public function isBirthday($date = null): bool
    {
        $other = $this->resolveCarbon($date);

        return $other->month === $this->month && $other->day === $this->day;
    }

    /**
     * Keep the date and time, and change the timezone (and so the instant).
     *
     * @param DateTimeZone|string $value
     */
    public function shiftTimezone($value): static
    {
        $dateTime = $this->rawFormat('Y-m-d H:i:s.u');

        return $this->setTimezone($value)->modify($dateTime);
    }

    /**
     * Determine if the Hijri year has 355 days.
     */
    public function isLeapYear(): bool
    {
        return HijriCalendar::isLeapYear($this->year);
    }

    /**
     * Determine if the given date is in the same Hijri unit (year, month,
     * quarter...) as the instance.
     *
     * @param string $unit
     * @param DateTimeInterface|string|null $date
     */
    public function isSameUnit($unit, $date = null): bool
    {
        if ($unit === 'year' || $unit === 'month') {
            $other = $this->resolveCarbon($date);

            return $other->year === $this->year && ($unit === 'year' || $other->month === $this->month);
        }

        return parent::isSameUnit($unit, $date ?? 'now');
    }

    /**
     * Determine if the given date is in the same Hijri month as the instance.
     *
     * @param DateTimeInterface|string|null $date
     * @param bool $ofSameYear
     */
    public function isSameMonth($date = null, $ofSameYear = true): bool
    {
        $other = $this->resolveCarbon($date);

        return $other->month === $this->month && (! $ofSameYear || $other->year === $this->year);
    }

    /**
     * Get a Hijri instance with the same adjustment from a date or a string.
     *
     * @param DateTimeInterface|string|null $date
     */
    protected function resolveCarbon($date = null): self
    {
        $resolved = parent::resolveCarbon($date);
        $adjustment = $this->getConversionAdjustment();

        if ($resolved instanceof self && $resolved->getConversionAdjustment() === $adjustment) {
            return $resolved;
        }

        return static::fromGregorian($resolved, null, $adjustment);
    }

    /**
     * Format the instance as a string (Hijri date).
     */
    public function __toString(): string
    {
        /** @var mixed $format */
        $format = $this->localToStringFormat ?? null;

        if ($format instanceof Closure) {
            return $format($this);
        }

        if (! is_string($format) || $format === '') {
            $format = CarbonInterface::DEFAULT_TO_STRING_FORMAT;
        }

        return $this->format($format);
    }

    /**
     * Format the instance as a Hijri date string (Y-m-d).
     */
    public function toDateString(): string
    {
        return $this->format('Y-m-d');
    }

    /**
     * Format the instance as a Hijri date and time string.
     *
     * @param string $unitPrecision
     */
    public function toDateTimeString($unitPrecision = 'second'): string
    {
        return $this->format('Y-m-d ' . static::getTimeFormatByPrecision($unitPrecision));
    }

    /**
     * Format the instance as a readable Hijri date (M j, Y).
     */
    public function toFormattedDateString(): string
    {
        return $this->format('M j, Y');
    }

    /**
     * Format the instance as a readable Hijri date with the weekday.
     */
    public function toFormattedDayDateString(): string
    {
        return $this->format('D, M j, Y');
    }

    /**
     * Format the instance as a readable Hijri date and time with the weekday.
     */
    public function toDayDateTimeString(): string
    {
        return $this->format('D, M j, Y g:i A');
    }

    /**
     * Get the instance as an array of Hijri values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['formatted'] = $this->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);

        return $array;
    }

    /**
     * Get the Gregorian ISO-8601 string of the instance, as used for JSON.
     *
     * @param bool $keepOffset
     */
    public function toISOString($keepOffset = false): ?string
    {
        return $this->toGregorian()->toISOString($keepOffset);
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        // Older Carbon 2 releases serialize with __sleep() only.
        $data = method_exists(Carbon::class, '__serialize') ? parent::__serialize() : [
            'timezone' => $this->getTimezone()->getName(),
        ];
        $data['date'] = $this->rawFormat('Y-m-d H:i:s.u');

        if (! isset($data['dumpLocale']) && $this->hasLocalTranslator()) {
            $data['dumpLocale'] = $this->locale();
        }

        if (isset($data['dumpDateProperties']['date'])) {
            $data['dumpDateProperties']['date'] = $data['date'];
        }

        $data['hijriAdjustment'] = $this->hijriAdjustment;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        if (method_exists(Carbon::class, '__unserialize')) {
            parent::__unserialize($data);
        } else {
            $this->__construct($data['date'], $data['timezone']);

            if (isset($data['dumpLocale'])) {
                $this->locale($data['dumpLocale']);
            }
        }

        $this->hijriAdjustment = $data['hijriAdjustment'] ?? null;
    }

    /**
     * Get the units used by isoFormat(), reading the day, month and year
     * through the Hijri values.
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
     * Get a translation message, with the Hijri month names.
     *
     * @param mixed $translator
     * @return mixed
     */
    public function getTranslationMessage(
        string $key,
        ?string $locale = null,
        ?string $default = null,
        $translator = null
    ) {
        if ($translator === null) {
            $translator = $this->getLocalTranslator();

            if ($translator instanceof AbstractTranslator) {
                $translator = self::getHijriTranslator($translator);
            }
        }

        return parent::getTranslationMessage($key, $locale, $default, $translator);
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

    /**
     * Format the instance with Hijri values.
     *
     * The date characters read the Hijri date: d, j, m, n, Y, y, t (days in
     * the Hijri month), z (day of the Hijri year, from 0), L (Hijri leap
     * year), S (English suffix of the Hijri day), and c and r (built from the
     * Hijri date). l, D, F and M print the localized weekday and Hijri month
     * names. Other characters (time, timezone, U, w, N, o, W...) are
     * formatted by Carbon. rawFormat() formats the Gregorian date.
     */
    public function format($format): string
    {
        return parent::format($this->toHijriFormat((string) $format));
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
        ['year' => $year, 'month' => $month, 'day' => $day] = $this->getHijriDate();

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
                    $dayOfYear += HijriCalendar::daysInMonth($year, $previous);
                }

                return (string) $dayOfYear;
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
                return (string) HijriCalendar::daysInMonth($year, $month);
            case 'L':
                return HijriCalendar::isLeapYear($year) ? '1' : '0';
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
