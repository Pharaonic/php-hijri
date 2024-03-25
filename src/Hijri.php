<?php

namespace Pharaonic\Hijri;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Month;
use Carbon\Translator;
use Carbon\WeekDay;
use DateTimeInterface;
use DateTimeZone;

class Hijri extends Carbon
{
    /**
     * Hijri Months List (Arabic)
     *
     * @var array
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
        'ذو الحِجّة'
    ];

    /**
     * Translated Hijri Months List (Not Arabic)
     *
     * @var array
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
        'Dhu Al-Hijjah'
    ];

    /**
     * Hijri Instance
     *
     * @var Hijri|null
     */
    protected static $HIJRI_INSTANCE;

    /**
     * CURRENT DAY NUMBER
     *
     * @var null|object
     */
    protected $CURRENT_DAY = null;


    
    /**
     * Getting an instance of Hijri class.
     *
     * @return Hijri
     */
    public static function getInstance()
    {
        return self::$HIJRI_INSTANCE ?? self::$HIJRI_INSTANCE = new self;
    }

    public function prepare(Hijri $obj)
    {
        return $obj->locale($this->getLocale())->adjustment()->convertToHijri();
    }

    /**
     * Adjust Hijri Days
     *
     * @return Hijri
     */
    private function adjustment()
    {
        $this->CURRENT_DAY = $this->dayOfWeek;

        // Adjust the current Carbon days
        $value = self::getHijriAdjustment();

        if ($value > 0)
            $this->addDays($value);
        else
            $this->subDays($value * -1);

        return $this;
    }

    /**
     * Convert current Carbon to Hijri
     *
     * @return Carbon
     */
    private function convertToHijri()
    {
        // Convert To Julian
        $jd = gregoriantojd($this->month, $this->day, $this->year);

        // Convert To Hijri
        $y = 10631.0 / 30.0;
        $shift = 8.01 / 60.0;

        $z = $jd - 1948084;
        $cyc = floor($z / 10631.0);
        $z = $z - 10631 * $cyc;
        $j = floor(($z - $shift) / $y);
        $z = $z - floor($j * $y + $shift);

        $year = 30 * $cyc + $j;
        $month = (int)floor(($z + 28.5001) / 29.5);
        if ($month === 13) $month = 12;
        $day = $z - floor(29.5001 * $month - 29);

        // Set Day & Month & Year
        $this->day($day);
        $this->month($month);
        $this->year($year);

        return $this;
    }

    

    /**
     * Create a carbon instance from a string.
     *
     * This is an alias for the constructor that allows better fluent syntax
     * as it allows you to do Carbon::parse('Monday next week')->fn() rather
     * than (new Carbon('Monday next week'))->fn().
     *
     * @param string|DateTimeInterface|null $time
     * @param DateTimeZone|string|null      $tz
     *
     * @throws InvalidFormatException
     *
     * @return static
     */
    public static function parse(DateTimeInterface|WeekDay|Month|string|int|float|null $time = null, DateTimeZone|string|int|null $timezone = null): static
    {
        return self::$HIJRI_INSTANCE->prepare(parent::parse($time, $timezone));
    }

    /**
     * Get/set the locale for the current instance.
     *
     * @param string|null $locale
     * @param string      ...$fallbackLocales
     *
     * @return $this|string
     */
    public function locale(?string $locale = null, string ...$fallbackLocales): static|string
    {
        if ($locale === null) {
            return $this->getTranslatorLocale();
        }

        if (!$this->localTranslator || $this->getTranslatorLocale($this->localTranslator) !== $locale) {
            $translator = Translator::get($locale);

            if (!empty($fallbackLocales)) {
                $translator->setFallbackLocales($fallbackLocales);

                foreach ($fallbackLocales as $fallbackLocale) {
                    $messages = Translator::get($fallbackLocale)->getMessages();

                    if (isset($messages[$fallbackLocale])) {
                        $translator->setMessages($fallbackLocale, $messages[$fallbackLocale]);
                    }
                }
            }

            $is_arabic = substr($translator->getLocale(), 0, 2) == 'ar';

            $translator->setTranslations([
                'months' => $is_arabic ? self::$HIJRI_MONTHS : self::$TRANS_HIJRI_MONTHS,
                'months_short' => $is_arabic ? self::$HIJRI_MONTHS : self::$TRANS_HIJRI_MONTHS,
            ]);

            $this->setLocalTranslator($translator);
        }

        return $this;
    }

    /**
     * Get the translation of the current week day name (with context for languages with multiple forms).
     *
     * @param string|null $context      whole format string
     * @param string      $keySuffix    "", "_short" or "_min"
     * @param string|null $defaultValue default value if translation missing
     *
     * @return string
     */
    public function getTranslatedDayName(?string $context = null, string $keySuffix = '', ?string $defaultValue = null): string
    {
        return $this->getTranslatedFormByRegExp('weekdays', $keySuffix, $context, $this->CURRENT_DAY, $defaultValue ?: $this->englishDayOfWeek);
    }

    protected function getTranslatedFormByRegExp($baseKey, $keySuffix, $context, $subKey, $defaultValue)
    {
        $key = $baseKey . $keySuffix;
        $standaloneKey = "{$key}_standalone";
        $baseTranslation = $this->getTranslationMessage($key);

        if ($baseTranslation instanceof Closure) {
            return $baseTranslation($this, $context, $subKey) ?: $defaultValue;
        }

        if (
            $this->getTranslationMessage("$standaloneKey.$subKey") &&
            (!$context || ($regExp = $this->getTranslationMessage("{$baseKey}_regexp")) && !preg_match($regExp, $context))
        ) {
            $key = $standaloneKey;
        }

        return $this->getTranslationMessage("$key.$subKey", null, $defaultValue);
    }

    /**
     * Get the translation of the current month day name (with context for languages with multiple forms).
     *
     * @param string|null $context      whole format string
     * @param string      $keySuffix    "" or "_short"
     * @param string|null $defaultValue default value if translation missing
     *
     * @return string
     */
    public function getTranslatedMonthName(?string $context = null, string $keySuffix = '', ?string $defaultValue = null): string
    {
        return $this->getTranslatedFormByRegExp('months', $keySuffix, $context, $this->month - 1, $defaultValue ?: $this->englishMonth);
    }

    /**
     * Returns the formatted date string on success or FALSE on failure.
     *
     * @param string $format
     *
     * @return string
     */
    public function format(string $format): string
    {
        return str_replace([
            $this->englishDayOfWeek,
            $this->englishMonth,

            $this->shortEnglishDayOfWeek,
            $this->shortEnglishMonth
        ], [
            $this->dayName,
            $this->monthName,

            $this->shortDayName,
            $this->monthName,
        ], parent::format($format));
    }
}
