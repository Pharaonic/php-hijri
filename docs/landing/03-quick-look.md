---
view: components.packages.quick-look
title: A quick look
subtitle: Convert a Gregorian date to Hijri and back again.
file: index.php
language: php
code: |
  use Carbon\Carbon;
  use Pharaonic\Hijri\HijriCarbon;

  Carbon::mixin(HijriCarbon::class);

  Carbon::parse('2024-03-11')->toHijri()->isoFormat('dddd D MMMM YYYY');
  // "Monday 1 Ramadan 1445"

  Carbon::fromHijri(1445, 10, 1)->toDateString();
  // "2024-04-10"
---
