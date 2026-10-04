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

### Localized Months {icon="translate"}
Arabic month names for `ar` locales, English transliteration for the rest.
:::

:::info Quick Tip
The `Hijri` class extends `Carbon\Carbon`, so `format()`, `isoFormat()`, `locale()` and the `year`/`month`/`day` properties all work on a converted date.
:::
