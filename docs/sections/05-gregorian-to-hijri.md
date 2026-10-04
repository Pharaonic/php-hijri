## Gregorian to Hijri

There are three ways to turn a Gregorian date into a `Hijri` instance. All of them return a `Pharaonic\Hijri\Hijri` object.

### From a Carbon Instance

`toHijri()` is added to Carbon by the mixin. It keeps the time and timezone of the original date:

```php title="index.php"
use Carbon\Carbon;

Carbon::parse('2024-03-11 09:30:00')->toHijri()->format('Y-m-d H:i'); // "1445-09-01 09:30"
```

Pass an adjustment to override the global one for this call only:

```php
Carbon::parse('2024-03-11')->toHijri(0)->format('Y-m-d'); // "1445-09-02"
```

### From a String or DateTime

`Hijri::parse()` accepts anything `Carbon::parse()` accepts (a string, a `DateTimeInterface`, or `null` for now) plus an optional timezone. It doesn't need the mixin:

```php title="index.php"
use Pharaonic\Hijri\Hijri;

Hijri::parse('2024-03-11')->format('Y-m-d'); // "1445-09-01"
Hijri::parse()->isoFormat('D MMMM YYYY');   // today, in Hijri
```

### With a Timezone and Adjustment

`Hijri::fromGregorian()` is the full form of `parse()`, with a third `$adjustment` argument:

```php
Hijri::fromGregorian('2024-03-11', 'Asia/Riyadh', 0)->format('Y-m-d e');
// "1445-09-02 Asia/Riyadh"
```

:::warning Timezones Matter
The conversion uses the calendar date in the instance's timezone. Late at night a UTC date can be a different day than in your users' timezone, so convert dates in the timezone you display them in.
:::
