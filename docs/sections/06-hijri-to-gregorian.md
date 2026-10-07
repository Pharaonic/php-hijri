## Hijri to Gregorian

These methods take Hijri values and return a regular Gregorian `Carbon\Carbon` instance, ready to store, compare or do math with.

### From Hijri Parts

`fromHijri(int $year, int $month, int $day, $tz = null, ?int $adjustment = null)` builds the date at midnight:

```php title="index.php"
use Carbon\Carbon;

Carbon::fromHijri(1445, 9, 1)->toDateString();  // "2024-03-11" (1 Ramadan 1445)
Carbon::fromHijri(1445, 10, 1)->toDateString(); // "2024-04-10" (1 Shawwal 1445)
Carbon::fromHijri(1446, 1, 1)->toDateString();  // "2024-07-08" (1 Muharram 1446)
```

### From a Hijri String

`parseHijri(string $date, $tz = null, ?int $adjustment = null)` accepts `YYYY-MM-DD`, optionally followed by a space or `T` and an `HH:MM` or `HH:MM:SS` time:

```php title="index.php"
Carbon::parseHijri('1445-09-01')->toDateTimeString();          // "2024-03-11 00:00:00"
Carbon::parseHijri('1445-09-01 20:15')->toDateTimeString();    // "2024-03-11 20:15:00"
Carbon::parseHijri('1413-08-08 19:30:45')->toDateTimeString(); // "1993-02-01 19:30:45"

Carbon::parseHijri('1445-12-10', 'Asia/Riyadh')->format('Y-m-d e');
// "2024-06-17 Asia/Riyadh"
```

Both methods are also available statically on the `Hijri` class without the mixin: `Hijri::fromHijri(...)` and `Hijri::parseHijri(...)`. They still return `Carbon\Carbon`.

### From a Hijri Instance

`toGregorian()` turns a `Hijri` instance back into a Gregorian `Carbon\Carbon`. It returns the exact date, time and timezone the instance was converted from, and remembers the adjustment used for that conversion:

```php title="index.php"
use Pharaonic\Hijri\Hijri;

$hijri = Hijri::fromGregorian('2024-03-11 20:15', 'Asia/Riyadh', 1);

$hijri->format('Y-m-d');                        // "1445-09-03"
$hijri->toGregorian()->format('Y-m-d H:i e');  // "2024-03-11 20:15 Asia/Riyadh"
```

Prefer it over `Hijri::fromHijri($hijri->year, $hijri->month, $hijri->day)`, which uses the global adjustment and drops the time.

If the instance was changed after the conversion (`addDay()`, `setTime()`...), its current year, month and day are converted as a Hijri date with the same adjustment, and its time and timezone are kept. That throws `InvalidHijriDateException` if the changed date doesn't exist in the Hijri calendar.

### Invalid Input

Both methods throw `Pharaonic\Hijri\Exception\InvalidHijriDateException` (an `InvalidArgumentException`) when the input can't be a Hijri date:

```php title="index.php"
use Pharaonic\Hijri\Exception\InvalidHijriDateException;

try {
    Carbon::fromHijri(1445, 2, 30); // Safar has 29 days
} catch (InvalidHijriDateException $e) {
    $e->getMessage(); // "Invalid Hijri date: 1445-02-30."
}

Carbon::parseHijri('11/03/1445');
// InvalidHijriDateException: Hijri date must use YYYY-MM-DD with an optional HH:MM[:SS] time part.
```
