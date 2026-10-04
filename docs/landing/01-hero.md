---
view: components.packages.package-hero
badges:
  - label: PHP Package
    color: blue
  - label: "{package.latestVersionLabel}"
    color: green
  - label: "{package.license} License"
    color: purple
  - label: "{package.downloadsShort}+ downloads"
    color: blue
eyebrow: "{package.name}"
title: Hijri dates
highlight: the Carbon way
buttons:
  - label: View Full Documentation
    href: "{card.docsUrl}"
    style: primary
    icon: arrow-right
  - label: View on GitHub
    href: "{package.githubUrl}"
    style: ghost
    external: true
install: "{card.install}"
labels:
  copy: Copy
  copied: Copied!
---

{package.name} adds the Islamic calendar to Carbon. Call `toHijri()` on any date to get its Hijri day, month, and year, turn a Hijri date back into a Gregorian Carbon instance with `fromHijri()` or `parseHijri()`, and format month names in Arabic or English with the formatting methods you already use.
