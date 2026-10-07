## Real-World Examples

### 1. Ramadan Banner

Show a banner only while it's Ramadan (month 9):

```php title="index.php"
use Carbon\Carbon;

$today = Carbon::now()->toHijri();

if ($today->month === 9) {
    echo "Ramadan Kareem! Day {$today->day} of Ramadan {$today->year}.";
}
```

### 2. Countdown to Eid al-Fitr

Eid al-Fitr is 1 Shawwal (month 10). Convert it back to Gregorian and let Carbon count the days:

```php title="src/EidCountdown.php"
use Carbon\Carbon;

function daysUntilEidAlFitr(Carbon $today): int
{
    $hijri = $today->toHijri();
    $year = $hijri->month >= 10 ? $hijri->year + 1 : $hijri->year;

    return $today->copy()->startOfDay()->diffInDays(Carbon::fromHijri($year, 10, 1));
}

daysUntilEidAlFitr(Carbon::parse('2024-03-20')); // 21
```

### 3. Validating a Hijri Date From a Form

Check the parts with `HijriCalendar` before converting, then store the Gregorian date:

```php title="src/BirthDateInput.php"
use Carbon\Carbon;
use Pharaonic\Hijri\Calendar\HijriCalendar;

$year = (int) $_POST['hijri_year'];
$month = (int) $_POST['hijri_month'];
$day = (int) $_POST['hijri_day'];

if (! HijriCalendar::isValidDate($year, $month, $day)) {
    $errors[] = sprintf('That month only has %d days.', HijriCalendar::daysInMonth($year, $month));
} else {
    $birthDate = Carbon::fromHijri($year, $month, $day)->toDateString(); // store as DATE
}
```

### 4. Bilingual Date Line

Print the same Hijri date in Arabic and English side by side. `copy()` keeps the original instance's locale untouched:

```php title="index.php"
$hijri = Carbon::parse('2024-03-11')->toHijri();

echo $hijri->copy()->locale('ar')->isoFormat('D MMMM YYYY'); // "1 رَمضان 1445"
echo ' / ';
echo $hijri->isoFormat('D MMMM YYYY');                       // "1 Ramadan 1445"
```

### 5. Monthly Renewals on the Hijri Calendar

Renew a subscription on the same Hijri day each month. `addMonthsNoOverflow()` stops at the 29th when the next month is shorter, and `toGregorian()` gives the date to store:

```php title="src/Renewals.php"
use Carbon\Carbon;

$start = Carbon::parse('2024-03-11')->toHijri(); // 1 Ramadan 1445

$renewals = [];

for ($month = 1; $month <= 3; $month++) {
    $renewals[] = $start->copy()->addMonthsNoOverflow($month)->toGregorian()->toDateString();
}

// ["2024-04-10", "2024-05-09", "2024-06-08"]
```

### 6. Dual-Calendar Date Helper

A small helper class that formats a stored Gregorian timestamp in both calendars, used from a plain PHP template. The Gregorian side is numeric on purpose (see [Troubleshooting](#troubleshooting)):

- ===Helper Class

  ```php title="src/DualDate.php"
  <?php

  namespace App;

  use Carbon\Carbon;
  use Pharaonic\Hijri\HijriCarbon;

  Carbon::mixin(HijriCarbon::class);

  final class DualDate
  {
      public function __construct(
          private Carbon $date,
          private string $locale = 'en',
      ) {
      }

      public function gregorian(): string
      {
          return $this->date->format('d/m/Y');
      }

      public function hijri(): string
      {
          return $this->date->toHijri()->locale($this->locale)->isoFormat('D MMMM YYYY');
      }
  }
  ```

- ===Template

  ```php title="templates/article.php"
  <?php $date = new App\DualDate(Carbon\Carbon::parse($article['published_at']), 'ar'); ?>

  <time datetime="<?= $article['published_at'] ?>">
      <?= $date->hijri() ?> — <?= $date->gregorian() ?>
  </time>
  ```
