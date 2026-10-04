## Day Adjustment

The package has no config file. Its only setting is the **day adjustment**: a whole number of days added to the Gregorian date before it's converted to Hijri (and subtracted when converting back).

The conversion uses the tabular Islamic calendar, which can be a day or two off from the dates announced after local moon sighting. The adjustment lets you line results up with your region.

### Default Value

The default adjustment is `-1`, which matches the package's historic results:

```php title="index.php"
use Pharaonic\Hijri\Hijri;

Hijri::getInstance()->getHijriAdjustment(); // -1
```

### Change It Globally

Set it once at boot. Every later conversion that doesn't pass its own adjustment uses this value:

```php title="bootstrap.php"
use Pharaonic\Hijri\Hijri;

Hijri::getInstance()->setHijriAdjustment(0);
```

With the mixin registered, the same methods are available on any Carbon instance:

```php
Carbon::now()->setHijriAdjustment(0);
Carbon::now()->getHijriAdjustment(); // 0
```

### Override It Per Call

Every conversion method accepts an `$adjustment` argument. It affects that call only and never changes the global value:

```php title="index.php"
$date = Carbon::parse('2024-03-11');

$date->toHijri()->format('Y-m-d');  // "1445-09-01" (global -1)
$date->toHijri(0)->format('Y-m-d'); // "1445-09-02"

Carbon::fromHijri(1445, 9, 1)->toDateString();          // "2024-03-11"
Carbon::fromHijri(1445, 9, 1, null, 0)->toDateString(); // "2024-03-10"
```

:::warning Shared State
The global adjustment is a static value shared by the whole PHP process. In long-running workers (queues, Octane, Swoole), set it once at boot rather than changing it per request, and use the per-call argument for one-off conversions.
:::
