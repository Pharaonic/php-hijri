# Changelog

All notable changes to this project will be documented in this file.

## 8.3.5 - 2026-10-07

### Added
- `Hijri::toGregorian()` returns the Gregorian date of a `Hijri` instance as a `Carbon\Carbon`. For an unchanged instance it's the exact date, time and timezone it was converted from, so a per-call adjustment is kept: `Hijri::fromGregorian('2024-03-11', null, 1)->toGregorian()` returns `2024-03-11`, while `Hijri::fromHijri($h->year, $h->month, $h->day)` used the global adjustment and returned `2024-03-13`. An instance changed after the conversion (`addDay()`, `setTime()`...) has its current year, month and day converted as a Hijri date with the same adjustment, keeping its time and timezone.

## 8.3.4 - 2026-10-07

### Fixed
- `fromHijri()` and `parseHijri()` now return the right Gregorian date in every Hijri year. Before, they used different leap years than `toHijri()`: dates in years such as 1426 and 1456 came out one day early (`Carbon::fromHijri(1426, 1, 1, null, 0)` returned `2005-02-09` instead of `2005-02-10`), and 30 Dhu Al-Hijjah 1425 was rejected although `toHijri()` returns it. `HijriCalendar::isLeapYear()`, `daysInMonth()` and `isValidDate()` now use the same leap years as `toHijri()`: 2, 5, 7, 10, 13, 15, 18, 21, 24, 26 and 29 of each 30-year cycle (15 instead of 16). `toHijri()` results don't change.

## 8.3.3 - 2026-10-07

### Fixed
- Converting a date to Hijri no longer changes the month names of other Carbon dates. Before, after the first conversion in a process, every Gregorian date printed with month names (`F`, `MMMM`, `monthName`) showed Hijri names, e.g. `11 Rabi' Al-Awwal 2024` instead of `11 March 2024`, and each conversion got slower. `Hijri` now uses its own translator per locale, built from Carbon's translations for that locale.
- The 29th of Safar is no longer turned into the 1st of Rabi' Al-Awwal. Before, `2020-10-16` (adjustment `0`) converted to `1442-03-01` instead of `1442-02-29`. `year`, `month`, `day`, `format()` and `isoFormat()` now return the converted Hijri date for every day. In `format()`, `t`, `z` and `L` now give the Hijri month length, day of year and leap year.
- `dayOfWeek`, `dayOfWeekIso` and weekday checks (`isTuesday()`, `isWeekend()`...) now follow the original date, like `dayName` already did. Before, `Carbon::parse('2025-10-07')->toHijri(0)->isTuesday()` returned `false`.

## 8.3.2 - 2026-10-06

### Added
- Carbon 3 support. `nesbot/carbon` now accepts `^2.62.1 || ^3.0`, and the test suite passes on both Carbon 2 and Carbon 3.

## 8.3.1 - 2026-10-05

### Fixed
- Hijri month names no longer silently fall back to Gregorian names (e.g. `September` instead of `Ramadan`) on older Carbon releases. The minimum Carbon version is raised from `^2.20` to `^2.62.1`: `Carbon\AbstractTranslator`, which the month-name override relies on, only exists since Carbon 2.55.0, and Carbon releases before 2.62.1 fail on PHP 8.2+.

### Changed
- CI runs the test suite against both the lowest and the highest allowed dependency versions.

## Unreleased

### Added
- Gregorian to Hijri conversion core extracted from the public Carbon API.
- Hijri to Gregorian conversion through `fromHijri()`.
- Hijri string parsing through `parseHijri()`.
- `HijriCalendar` helpers for validation, leap years, and month lengths.
- Per-call Hijri adjustment support.
- Julian Day conversion implemented internally without requiring `ext-calendar`.
- PHPUnit, PHPStan, PHPCS, and GitHub Actions quality checks.

### Changed
- PHP support is intentionally scoped to PHP 8.3.x for this release line.
- Carbon support is intentionally scoped to Carbon 2.x.
- Internal conversion logic is separated from the Carbon-facing API.

### Compatibility
- `Carbon::mixin(HijriCarbon::class)` remains supported.
- `->toHijri()` remains supported.
- `setHijriAdjustment()` and `getHijriAdjustment()` remain supported.
