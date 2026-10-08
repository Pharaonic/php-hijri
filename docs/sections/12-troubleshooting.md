## Troubleshooting

### Call to undefined method Carbon\Carbon::toHijri()

**Cause:** the mixin isn't registered, or it was registered after the call.

**Fix:** call `Carbon::mixin(HijriCarbon::class)` once at boot, before any conversion. If you can't control boot order, use `Hijri::parse($date)` instead, which works without the mixin.

### The Hijri date is one day off

**Cause:** the package uses the tabular Islamic calendar with a default adjustment of `-1`. Local calendars based on moon sighting (or Umm al-Qura) can differ by a day or two.

**Fix:** change the global adjustment at boot with `Hijri::getInstance()->setHijriAdjustment(0)` (or another value), or pass an adjustment to a single call: `$date->toHijri(0)`. See [Day Adjustment](#adjustment).

### The date is right in one timezone and wrong in another

**Cause:** the conversion uses the calendar day of the instance's timezone. A UTC timestamp just before midnight is already the next day in Riyadh.

**Fix:** set the timezone before converting: `$date->setTimezone('Asia/Riyadh')->toHijri()`, or `Hijri::fromGregorian($value, 'Asia/Riyadh')`.

### Month names are in English instead of Arabic

**Cause:** Arabic names are used only when the locale starts with `ar`.

**Fix:** call `->locale('ar')` on the `Hijri` instance, or `Carbon::setLocale('ar')` before converting.

### Another library gets the Hijri date instead of the Gregorian one

**Cause:** `format()` (and `toDateString()`, `(string)`...) print the Hijri date. Code that reads a `DateTimeInterface` through `format('Y-m-d')`, such as a `datetime` column cast or `new DateTime($date->format(...))`, receives `"1445-09-01"`.

**Fix:** pass `$hijri->toGregorian()` to that code. `toISOString()` and `json_encode()` already give the Gregorian date.

:::warning
Don't store a `Hijri` instance's `format('Y-m-d')` in a `DATE` column. Store the Gregorian date and convert when you display it.
:::

### Gregorian dates show Hijri month names

**Cause:** releases before this patch set the Hijri month names on Carbon's shared translator. After the first conversion in a process, ordinary Gregorian dates that print month names (`F`, `M`, `MMMM`, `monthName`) showed Hijri names too, e.g. `"Rabi' Al-Awwal"` instead of `"March"`.

**Fix:** update to the latest patch release with `composer update pharaonic/php-hijri`. `Hijri` now uses its own translator, and Gregorian dates keep their month names.

### InvalidHijriDateException when parsing

**Cause:** `parseHijri()` accepts only `YYYY-MM-DD` with an optional `HH:MM` or `HH:MM:SS` time, and `fromHijri()` rejects days that don't exist (for example 30 Safar).

**Fix:** normalize the input to `YYYY-MM-DD` first, and check the parts with `HijriCalendar::isValidDate()`.

### `modify('+1 month')` moves a Gregorian month

**Cause:** `modify()` uses PHP's relative formats, which follow the Gregorian calendar.

**Fix:** use `addMonths()`, `addYears()`, `add()` or `sub()`, which follow Hijri months and years. See [Date Math & Comparisons](#date-math).
