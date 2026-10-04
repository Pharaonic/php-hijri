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

### Date math gives strange results on a Hijri instance

**Cause:** a `Hijri` instance is a Carbon object whose year, month and day hold Hijri numbers. Carbon still applies Gregorian rules to it, so `addDays()`, `diffInDays()`, `diffForHumans()` and comparisons don't follow the Hijri calendar, and calling `toHijri()` on it converts it a second time.

**Fix:** do all math and comparisons on the Gregorian `Carbon` date, and call `toHijri()` only for display. To start from Hijri values, convert them with `Carbon::fromHijri()` first.

:::warning
Don't store a `Hijri` instance's `format('Y-m-d')` in a `DATE` column. Store the Gregorian date and convert when you display it.
:::

### Gregorian dates show Hijri month names

**Cause:** `Hijri` sets its month names on Carbon's shared translator. After the first conversion in a process, ordinary Gregorian dates that print month names (`F`, `M`, `MMMM`, `monthName`) show Hijri names too, e.g. `"Rabi' Al-Awwal"` instead of `"March"`.

**Fix:** until this is fixed in the package, print Gregorian dates with numeric formats (`d/m/Y`, `toDateString()`) in requests that also convert to Hijri.

### InvalidHijriDateException when parsing

**Cause:** `parseHijri()` accepts only `YYYY-MM-DD` with an optional `HH:MM` or `HH:MM:SS` time, and `fromHijri()` rejects days that don't exist (for example 30 Safar).

**Fix:** normalize the input to `YYYY-MM-DD` first, and check the parts with `HijriCalendar::isValidDate()`.
