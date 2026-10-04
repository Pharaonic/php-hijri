## Formatting & Locales

`Hijri` overrides Carbon's month translations, so `format()`, `isoFormat()` and `monthName` print Hijri month names instead of Gregorian ones.

### Month Names

Any locale starting with `ar` uses the Arabic names. Every other locale uses the English transliteration:

| # | Arabic | English |
| --- | --- | --- |
| 1 | مُحرَّم | Muharram |
| 2 | صفَر | Safar |
| 3 | ربيع الأول | Rabi' Al-Awwal |
| 4 | ربيع الآخر | Rabi' Al-Akher |
| 5 | جمادى الأول | Jumada Al-Awwal |
| 6 | جمادى الآخرة | Jumada Al-Akherah |
| 7 | رَجب | Rajab |
| 8 | شَعبان | Sha'aban |
| 9 | رَمضان | Ramadan |
| 10 | شوّال | Shawwal |
| 11 | ذو القِعدة | Dhu Al-Qi'dah |
| 12 | ذو الحِجّة | Dhu Al-Hijjah |

Short month formats (`M`, `MMM`) print the full name, since Hijri months have no standard abbreviations.

### Per-Date Locale

Call `locale()` on the converted date:

```php title="index.php"
$hijri = Carbon::parse('2024-03-11')->toHijri();

$hijri->locale('ar')->isoFormat('dddd D MMMM YYYY'); // "الاثنين 1 رَمضان 1445"
$hijri->locale('fr')->isoFormat('dddd D MMMM YYYY'); // "lundi 1 Ramadan 1445"
$hijri->locale('en')->isoFormat('dddd D MMMM YYYY'); // "Monday 1 Ramadan 1445"
```

Weekday names come from Carbon's own translations for the locale, so they're always localized.

### Global Locale

New `Hijri` instances take Carbon's global locale:

```php title="bootstrap.php"
Carbon::setLocale('ar');

Carbon::parse('2024-03-11')->toHijri()->isoFormat('LL'); // "1 رَمضان 1445"
```

### `format()` vs `isoFormat()`

Both are supported. `format()` replaces English day and month names in the output with the localized Hijri ones, so `l`, `D`, `F` and `M` work as expected:

```php
$hijri->format('l, j F Y'); // "Monday, 1 Ramadan 1445"
$hijri->format('D, M j');   // "Mon, Ramadan 1"
```

:::info Tip
Prefer `isoFormat()` for non-English output. It reads names straight from the translator, while `format()` relies on replacing the English names after formatting.
:::
