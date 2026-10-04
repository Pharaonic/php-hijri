---
view: components.packages.features
variant: compact
badge: Key Features
title: Everything you need to work with Hijri dates
subtitle: Register one Carbon mixin and every date in your app speaks Hijri.
items:
  - icon: arrow-long-right
    title: Gregorian to Hijri
    text: Call `toHijri()` on any Carbon date, or `Hijri::parse()` on any string Carbon understands.
  - icon: switch
    title: Hijri to Gregorian
    text: Build a Gregorian Carbon date from Hijri parts with `Carbon::fromHijri(1445, 9, 1)`.
  - icon: document
    title: Hijri String Parsing
    text: Parse `YYYY-MM-DD` Hijri strings with an optional time using `Carbon::parseHijri()`.
  - icon: translate
    title: Arabic and English Months
    text: Month names switch between Arabic and transliterated English based on the Carbon locale.
  - icon: plus-circle
    title: Day Adjustment
    text: Shift conversions by whole days to match local moon sighting, globally or per call.
  - icon: check-circle
    title: Calendar Validation
    text: "`HijriCalendar` checks leap years, month lengths, and whether a Hijri date exists."
---
