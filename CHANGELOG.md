# Changelog

All notable changes to this project will be documented in this file.

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
