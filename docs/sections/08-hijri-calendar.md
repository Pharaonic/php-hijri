## Hijri Calendar

`Pharaonic\Hijri\Calendar\HijriCalendar` exposes the rules of the tabular Hijri calendar the package uses. Use it to validate user input before converting.

```php title="index.php"
use Pharaonic\Hijri\Calendar\HijriCalendar;

HijriCalendar::isLeapYear(1445);        // true
HijriCalendar::daysInMonth(1445, 9);    // 30
HijriCalendar::daysInMonth(1445, 12);   // 30 (leap year)
HijriCalendar::daysInMonth(1446, 12);   // 29
HijriCalendar::daysInMonth(1445, 13);   // 0 (invalid month)

HijriCalendar::isValidDate(1445, 9, 1);  // true
HijriCalendar::isValidDate(1445, 2, 30); // false
HijriCalendar::isValidDate(1445, 13, 1); // false
```

### Calendar Rules

- Odd months (Muharram, Rabi' Al-Awwal, …) have 30 days; even months have 29.
- Dhu Al-Hijjah (month 12) has 30 days in a leap year and 29 otherwise.
- A year is a leap year when `(11 × year + 14) mod 30 < 11`, giving 11 leap years in every 30-year cycle.
- Years start at 1; year `0` and negative years are invalid.

:::info Observed vs Tabular
These are the arithmetic rules, not the result of moon sighting. Use the [day adjustment](#adjustment) to match a local calendar.
:::
