## Date Math & Comparisons

A `Hijri` instance holds the real date and time, and reads it in the Hijri calendar. Its timestamp, comparisons and day-based differences work with any other date, and month and year math follows Hijri month lengths.

### Adding and Subtracting

Days, weeks, hours and smaller units move the real date. Months, quarters, years, decades and centuries are Hijri ones:

```php title="index.php"
use Pharaonic\Hijri\Hijri;

$date = Hijri::fromGregorian('2023-09-15'); // 29 Safar 1445

$date->copy()->addDay()->format('Y-m-d');   // "1445-03-01"
$date->copy()->addMonth()->format('Y-m-d'); // "1445-03-29"
$date->copy()->subMonth()->format('Y-m-d'); // "1445-01-29"
$date->copy()->addYear()->format('Y-m-d');  // "1446-02-29"
```

`add()`, `sub()`, `CarbonInterval`, `DateInterval` and `CarbonPeriod` work the same way:

```php
use Carbon\CarbonInterval;

$date->copy()->add(CarbonInterval::months(2))->format('Y-m-d'); // "1445-04-29"
$date->copy()->add(new DateInterval('P1M2D'))->format('Y-m-d'); // "1445-04-01"
```

Like Carbon, a `Hijri` instance is mutable: use `copy()` to keep the original.

### Month Overflow

When the day doesn't exist in the target month (the 30th, in a 29-day month), the same Carbon settings apply. By default the date moves into the next month; the `NoOverflow` methods stop at the last day:

```php
$date = Hijri::fromGregorian('2023-08-17'); // 30 Muharram 1445, Safar has 29 days

$date->copy()->addMonth()->format('Y-m-d');           // "1445-03-01"
$date->copy()->addMonthNoOverflow()->format('Y-m-d'); // "1445-02-29"
```

### Setting Hijri Parts

`setDate()`, `year()`, `month()`, `day()` and the `year`, `month` and `day` properties take Hijri values and keep the time:

```php
$date = Hijri::fromGregorian('2024-03-11 09:15'); // 1 Ramadan 1445

$date->copy()->setDate(1446, 1, 10)->format('Y-m-d H:i'); // "1446-01-10 09:15"
$date->copy()->day(27)->format('Y-m-d');                  // "1445-09-27"
$date->copy()->month(12)->day(10)->toGregorian()->toDateString(); // "2024-06-17"
```

A day out of range moves the date, like `DateTime::setDate()`: `setDate(1445, 2, 30)` is 1 Rabi' Al-Awwal, as Safar 1445 has 29 days.

### Start and End of Units

`startOfMonth()`, `endOfMonth()`, `startOfYear()`, `endOfYear()`, the quarter, decade and century versions, and `firstOfMonth()`, `lastOfMonth()` and `nthOfMonth()` use Hijri months and years:

```php
use Carbon\Carbon;

$date = Hijri::fromGregorian('2024-03-21'); // 11 Ramadan 1445

$date->copy()->startOfMonth()->toGregorian()->toDateString(); // "2024-03-11"
$date->copy()->endOfYear()->format('Y-m-d H:i:s');            // "1445-12-30 23:59:59"
$date->copy()->lastOfMonth(Carbon::FRIDAY)->format('Y-m-d');  // "1445-09-26"
```

### Comparing With Other Dates

Comparisons use the real date, so a `Hijri` instance can be compared with any Carbon or `DateTime` date:

```php
$date = Hijri::fromGregorian('2024-03-11');

$date->eq(Carbon::parse('2024-03-11'));        // true
$date->diffInDays(Carbon::parse('2024-03-21')); // 10
$date->isPast();                                // true
$date->getTimestamp();                          // 1710115200 (2024-03-11 00:00 UTC)
```

`isSameMonth()`, `isSameYear()`, `isSameQuarter()`, `isCurrentMonth()` and `isBirthday()` compare Hijri months and years. `diffInMonths()`, `diffInQuarters()`, `diffInYears()` and `age` count Hijri months and years:

```php
$date->isSameMonth(Carbon::parse('2024-04-09'));  // true, 30 Ramadan 1445
$date->diffInMonths(Carbon::parse('2024-04-10')); // 1, 1 Shawwal 1445
Hijri::fromGregorian('1990-01-01')->age;          // age in Hijri years
```

`diffForHumans()` describes the real time span, with Carbon's (Gregorian) months and years.

### What Stays Gregorian

- `create()`, `createFromDate()`, `createFromFormat()` and `parse()` take Gregorian input, like `fromGregorian()`.
- `modify()` with a relative string (`'+1 month'`) uses PHP's Gregorian rules. Use `addMonths()` instead.
- `rawFormat()`, `toISOString()`, `toJSON()`, `json_encode()` and the RFC/ATOM strings give the Gregorian date, so APIs and other systems receive a standard date.
- `getTimestamp()` and the `U` format character are the real timestamp.
- ISO week values (`W`, `o`, `isoWeek`) are Gregorian ISO weeks.

To hand the date to code that expects a Gregorian date, use `toGregorian()` (or `toImmutable()` for a `CarbonImmutable`).
