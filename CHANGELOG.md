# Changelog

All notable changes to this project will be documented in this file.

## 8.2.1 - 2026-10-05

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
- PHP support is intentionally scoped to PHP 8.2.x for this release line.
- Carbon support is intentionally scoped to Carbon 2.x.
- Internal conversion logic is separated from the Carbon-facing API.

### Compatibility
- `Carbon::mixin(HijriCarbon::class)` remains supported.
- `->toHijri()` remains supported.
- `setHijriAdjustment()` and `getHijriAdjustment()` remain supported.
