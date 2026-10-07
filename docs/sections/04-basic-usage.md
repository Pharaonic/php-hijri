## Basic Usage

With the mixin registered, convert any Carbon date to Hijri:

```php title="index.php"
use Carbon\Carbon;

$hijri = Carbon::parse('2024-03-11 09:30:00')->toHijri();

$hijri->format('Y-m-d');                  // "1445-09-01"
$hijri->isoFormat('dddd D MMMM YYYY');    // "Monday 1 Ramadan 1445"
$hijri->isoFormat('LLLL');                // "Monday, Ramadan 1, 1445 9:30 AM"
```

The result is a `Pharaonic\Hijri\Hijri` instance. Its `year`, `month` and `day` hold the Hijri values, while the time and weekday stay the same as the original date:

```php
$hijri->year;      // 1445
$hijri->month;     // 9
$hijri->day;       // 1
$hijri->monthName; // "Ramadan"
$hijri->dayName;   // "Monday"
```

Date math follows the Hijri calendar, and the instance still compares with any other date:

```php
$hijri->copy()->addMonth()->format('Y-m-d');       // "1445-10-01"
$hijri->copy()->endOfMonth()->format('Y-m-d');     // "1445-09-30"
$hijri->diffInDays(Carbon::parse('2024-03-21 09:30')); // 10
$hijri->toGregorian()->toDateTimeString();         // "2024-03-11 09:30:00"
```

Go the other way with `fromHijri()`, which returns a regular Gregorian `Carbon` instance:

```php
Carbon::fromHijri(1445, 10, 1)->toDateString(); // "2024-04-10"
```

Or parse a Hijri date string:

```php
Carbon::parseHijri('1445-09-01 20:15')->toDateTimeString(); // "2024-03-11 20:15:00"
```

To get today's Hijri date:

```php
Carbon::now()->toHijri()->isoFormat('D MMMM YYYY');
```
