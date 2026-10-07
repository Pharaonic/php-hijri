## API Reference

### HijriCarbon Methods

Added to `Carbon\Carbon` by `Carbon::mixin(HijriCarbon::class)`, and available on `Pharaonic\Hijri\Hijri` directly.

| Method | Description | Returns |
| --- | --- | --- |
| `toHijri(?int $adjustment = null)` | Convert this Gregorian date to Hijri. | `Hijri` |
| `fromHijri(int $year, int $month, int $day, $tz = null, ?int $adjustment = null)` | Static. Build a Gregorian date at midnight from Hijri parts. | `Carbon` |
| `parseHijri(string $date, $tz = null, ?int $adjustment = null)` | Static. Parse a `YYYY-MM-DD[ HH:MM[:SS]]` Hijri string into a Gregorian date. | `Carbon` |
| `setHijriAdjustment(int $days)` | Set the global day adjustment. | `void` |
| `getHijriAdjustment()` | Get the global day adjustment (default `-1`). | `int` |

### Hijri Class

`Pharaonic\Hijri\Hijri` extends `Carbon\Carbon`, so every Carbon method is available too.

| Method | Description | Returns |
| --- | --- | --- |
| `Hijri::parse($time = null, $tz = null)` | Convert any Carbon-supported input to Hijri, using the global adjustment. | `Hijri` |
| `Hijri::fromGregorian($time = null, $tz = null, ?int $adjustment = null)` | Same as `parse()`, with a per-call adjustment. | `Hijri` |
| `toGregorian()` | The Gregorian date the instance was converted from, with its time, timezone and adjustment. See [Hijri to Gregorian](#hijri-to-gregorian). | `Carbon` |
| `Hijri::getInstance()` | The shared instance used to read and set the global adjustment. | `Hijri` |
| `locale(?string $locale = null, ...$fallbackLocales)` | Set the locale and switch month names between Arabic and English. Returns the locale when called with no argument. | `Hijri` \| `string` |
| `format($format)` | Carbon's `format()` with Hijri date values and localized Hijri month and day names. See [Formatting & Locales](#formatting). | `string` |
| `getTranslatedDayName($context = null, $keySuffix = '', $defaultValue = null)` | Localized weekday name of the original date. | `string` |

### Hijri Properties

The usual Carbon properties, read with Hijri values:

| Property | Description | Example |
| --- | --- | --- |
| `year` | Hijri year | `1445` |
| `month` | Hijri month (1–12) | `9` |
| `day` | Hijri day of month | `1` |
| `monthName` | Localized Hijri month name | `"Ramadan"` |
| `dayName` | Localized weekday name | `"Monday"` |
| `dayOfWeek` | Weekday of the original date, `0` (Sunday) to `6` (Saturday) | `1` |
| `dayOfWeekIso` | Weekday of the original date, `1` (Monday) to `7` (Sunday) | `1` |
| `hour`, `minute`, `second` | Unchanged from the source date | `9` |

Weekday checks such as `isMonday()`, `isWeekday()` and `isWeekend()` follow the original date too.

### HijriCalendar

All methods are static on `Pharaonic\Hijri\Calendar\HijriCalendar`.

| Method | Description | Returns |
| --- | --- | --- |
| `isLeapYear(int $year)` | Whether the Hijri year has 355 days. | `bool` |
| `daysInMonth(int $year, int $month)` | 29 or 30, or `0` for a month outside 1–12. | `int` |
| `isValidDate(int $year, int $month, int $day)` | Whether the Hijri date exists. | `bool` |

### Exceptions

| Class | Thrown by | When |
| --- | --- | --- |
| `Pharaonic\Hijri\Exception\InvalidHijriDateException` | `fromHijri()`, `parseHijri()`, `toGregorian()` of a changed instance | The Hijri date doesn't exist, or the string isn't `YYYY-MM-DD[ HH:MM[:SS]]`. |
