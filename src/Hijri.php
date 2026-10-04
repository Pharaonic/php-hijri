<?php

namespace Pharaonic\Hijri;

use Carbon\Carbon;
use Carbon\AbstractTranslator;
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

    /** @var int|null */
    protected $CURRENT_DAY = null;

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
        $obj->locale($this->getLocale());
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

        $this->setDate(
            $components['year'],
            $components['month'],
            $components['day']
        );

        return $this;
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
    public function locale(string $locale = null, ...$fallbackLocales): static|string
    {
        if ($locale === null) {
            return $this->getTranslatorLocale();
        }

        parent::locale($locale, ...$fallbackLocales);

        $translator = $this->getLocalTranslator();

        if ($translator instanceof AbstractTranslator) {
            $isArabic = substr($translator->getLocale(), 0, 2) === 'ar';

            $translator->setTranslations([
                'months' => $isArabic ? self::$HIJRI_MONTHS : self::$TRANS_HIJRI_MONTHS,
                'months_short' => $isArabic ? self::$HIJRI_MONTHS : self::$TRANS_HIJRI_MONTHS,
            ]);
        }

        return $this;
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

    public function format($format): string
    {
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
}
