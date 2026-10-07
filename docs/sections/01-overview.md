:::badges
- PHP Package {color=blue}
- {release.label} {color=green}
- {package.license} License {color=purple}
:::

# Hijri

Hijri (Islamic) calendar support for PHP, built on [Carbon](https://carbon.nesbot.com). Convert any Gregorian date to Hijri, build Gregorian dates from Hijri parts, parse Hijri strings, and format Hijri month names in Arabic or English with the Carbon methods you already know.

:::features
### Gregorian to Hijri {icon="arrow-long-right"}
`toHijri()` on any Carbon date, or `Hijri::parse()` on any date string.

### Hijri to Gregorian {icon="switch"}
`Carbon::fromHijri()` and `Carbon::parseHijri()` return regular Carbon dates.

### Hijri Date Math {icon="calendar"}
`addMonths()`, `startOfMonth()`, `diffInMonths()` and friends follow Hijri months and years.

### Localized Months {icon="translate"}
Arabic month names for `ar` locales, English transliteration for the rest.
:::

:::info Quick Tip
The `Hijri` class extends `Carbon\Carbon` and holds the real date, so it compares with any other date, and `format()`, `isoFormat()`, the `year`/`month`/`day` properties and date math all work in Hijri.
:::
