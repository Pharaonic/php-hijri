---
view: components.home.faq
badge: FAQ
title: "{package.name}"
highlight: Questions
subtitle: "Quick answers about installing and using {package.name}."
---

## What is {package.name}?

{card.description} It's a free, open-source {technology.name} package by Pharaonic.

## How do I install {package.name}?

Run `composer require {package.composer}` in your project's root directory.

## What does {package.name} require?

The latest release requires {package.requiresText}.

## Is {package.name} free to use?

Yes. {package.name} is open source under the {package.license} license, so you can use it in personal and commercial projects.

## Which Hijri calendar does {package.name} use?

It uses the tabular (arithmetic) Islamic calendar, the same 30-year cycle used by most software. Real-world dates can differ by a day or two from local moon sighting, so the package lets you shift results with a day adjustment.

## How do I show month names in Arabic?

Set an Arabic locale, either globally with `Carbon::setLocale('ar')` or per date with `->locale('ar')`. Any `ar` locale uses the Arabic month names; every other locale uses the English transliteration.

## Does it need the PHP calendar extension?

No. The Julian Day math is done in plain PHP, so `ext-calendar` isn't required.

## Where can I find the {package.name} documentation?

Read the [{package.name} documentation]({package.docsUrl}) for setup, configuration, and usage examples.

## How do I report a bug or contribute to {package.name}?

Open an issue or a pull request on [GitHub]({package.githubUrl}), or ask in the [Pharaonic Discord](https://discord.gg/XQG9RhvEvf).
