## Installation

Install the package with Composer and register the Carbon mixin once at boot.

### Requirements

- PHP 8.4.x
- Carbon 2.x or 3.x (`nesbot/carbon` ^2.62.1 || ^3.0)

No PHP extensions are required. The Julian Day math is implemented in plain PHP, so `ext-calendar` isn't needed.

### Composer Installation

```bash title="Terminal" no-line-numbers
composer require pharaonic/php-hijri
```

### Register the Carbon Mixin

`toHijri()`, `fromHijri()` and `parseHijri()` live in the `HijriCarbon` trait. Mix it into Carbon once, as early as possible in your app's bootstrap:

```php title="bootstrap.php"
use Carbon\Carbon;
use Pharaonic\Hijri\HijriCarbon;

Carbon::mixin(HijriCarbon::class);
```

In a Laravel app, put the same call in the `boot()` method of `App\Providers\AppServiceProvider`.

:::info Without the Mixin
You can skip the mixin and call the `Hijri` class directly: `Hijri::parse()`, `Hijri::fromGregorian()`, `Hijri::fromHijri()` and `Hijri::parseHijri()` work on their own.
:::

:::success Installation Complete
You're all set! Call `Carbon::now()->toHijri()` to get today's Hijri date.
:::
