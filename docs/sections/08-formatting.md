## Formatting & Locales

`Hijri` formats with its own copy of Carbon's translations for each locale, with Hijri month names in place of the Gregorian ones. `format()`, `isoFormat()` and `monthName` print Hijri month names, while regular Carbon dates in the same app keep their Gregorian names.

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

Both are supported. In `format()`, the date characters print Hijri values, and `l`, `D`, `F` and `M` print the localized weekday and Hijri month names:

```php
$hijri->format('l, j F Y'); // "Monday, 1 Ramadan 1445"
$hijri->format('D, M j');   // "Mon, Ramadan 1"
```

| Character | Prints |
| --- | --- |
| `d`, `j` | Hijri day, with and without a leading zero |
| `m`, `n` | Hijri month, with and without a leading zero |
| `Y`, `y` | Hijri year, four and two digits |
| `F`, `M` | Localized Hijri month name (both print the full name) |
| `l`, `D` | Localized weekday name, full and short |
| `w`, `N` | Weekday number (`0`–`6` from Sunday, `1`–`7` from Monday) |
| `t` | Number of days in the Hijri month (29 or 30) |
| `z` | Day of the Hijri year, starting from `0` |
| `L` | `1` if the Hijri year has 355 days, `0` otherwise |
| `S` | English ordinal suffix of the Hijri day (`st`, `nd`, `rd`, `th`) |
| `c`, `r` | ISO 8601 and RFC 2822 dates built from the Hijri values |

Time and timezone characters (`H`, `i`, `s`, `A`, `e`, `P`...), the timestamp (`U`) and ISO weeks (`W`, `o`) are unchanged. Escape a character with a backslash to print it as is: `format('\Y Y')` prints `"Y 1445"`.

`rawFormat()` formats the Gregorian date. `toDateString()`, `toDateTimeString()`, `toFormattedDateString()` and `(string)` print the Hijri date, while `toISOString()`, `toJSON()` and `json_encode()` give the Gregorian ISO-8601 date, so APIs return a standard date:

```php
$hijri = Carbon::parse('2024-03-11 09:30', 'UTC')->toHijri();

$hijri->toDateString();      // "1445-09-01"
$hijri->rawFormat('Y-m-d');  // "2024-03-11"
json_encode($hijri);         // "\"2024-03-11T09:30:00.000000Z\""
```

:::info Tip
Prefer `isoFormat()` for non-English output. It reads every name and pattern (`LL`, `LLLL`) from the locale's translations.
:::
