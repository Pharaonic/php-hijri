# Changelog

All notable changes to this project will be documented in this file.

## 8.1.2 - Unreleased

### Fixed
- Converting a date to Hijri no longer changes the month names of other Carbon dates. Before, after the first conversion in a process, every Gregorian date printed with month names (`F`, `MMMM`, `monthName`) showed Hijri names, e.g. `11 Rabi' Al-Awwal 2024` instead of `11 March 2024`, and each conversion got slower. `Hijri` now uses its own translator per locale, built from Carbon's translations for that locale.
- The 29th of Safar is no longer turned into the 1st of Rabi' Al-Awwal. Before, `2020-10-16` (adjustment `0`) converted to `1442-03-01` instead of `1442-02-29`. `year`, `month`, `day`, `format()` and `isoFormat()` now return the converted Hijri date for every day. In `format()`, `t`, `z` and `L` now give the Hijri month length, day of year and leap year.
- `dayOfWeek`, `dayOfWeekIso` and weekday checks (`isTuesday()`, `isWeekend()`...) now follow the original date, like `dayName` already did. Before, `Carbon::parse('2025-10-07')->toHijri(0)->isTuesday()` returned `false`.

## 8.1.1 - 2026-10-05

### Fixed
- Hijri month names no longer silently fall back to Gregorian names (e.g. `September` instead of `Ramadan`) on older Carbon releases. The minimum Carbon version is raised from `^2.20` to `^2.55`: `Carbon\AbstractTranslator`, which the month-name override relies on, only exists since Carbon 2.55.0.

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
- PHP support is intentionally scoped to PHP 8.1.x for this release line.
- Carbon support is intentionally scoped to Carbon 2.x.
- Internal conversion logic is separated from the Carbon-facing API.

### Compatibility
- `Carbon::mixin(HijriCarbon::class)` remains supported.
- `->toHijri()` remains supported.
- `setHijriAdjustment()` and `getHijriAdjustment()` remain supported.
