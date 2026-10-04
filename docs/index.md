---
name: Hijri

action:
  label: View on Packagist
  href: "{package.packagistUrl}"

views: components.packages

breadcrumbs:
  - label: Home
    href: route:home
  - label: Packages
    href: route:packages.index
  - label: "{technology.name} Packages"
    href: "url:/packages/{technology.slug}"
  - label: "{package.name}"

card:
  topic: localization
  icon: clock
  tags: hijri islamic calendar date datetime carbon ramadan arabic conversion
  description: Hijri (Islamic) date support for PHP built on Carbon. Convert Gregorian dates to Hijri and back, parse Hijri strings, and format month names in Arabic or English.

seo:
  title: "{package.name} - PHP Hijri (Islamic) Calendar for Carbon"
  description: "{package.name} is a PHP package that adds Hijri (Islamic) calendar conversion, parsing, and formatting to Carbon. {package.downloadsShort}+ downloads, {package.license} licensed."
  keywords: php hijri, islamic calendar, hijri date, carbon hijri, gregorian to hijri, hijri to gregorian, ramadan date php
  author: Pharaonic
  images:
    - "{package.cover}"
  openGraph:
    type: website
    siteName: Pharaonic
  twitter:
    card: summary_large_image

schema:
  "@type": SoftwareSourceCode
  name: "{package.name}"
  description: "{package.name} is a PHP package that adds Hijri (Islamic) calendar conversion, parsing, and formatting to Carbon."
  image: "{package.cover}"
  codeRepository: "{package.githubUrl}"
  programmingLanguage: PHP
  runtimePlatform: "{technology.name}"
  version: "{package.version}"
  datePublished: "{package.publishedAt}"
  dateModified: "{package.updatedAt}"
  license: "https://opensource.org/licenses/{package.license}"
  isAccessibleForFree: true
  sameAs:
    - "{package.githubUrl}"
    - "{package.packagistUrl}"
  author:
    "@id": url:/#organization
  publisher:
    "@id": url:/#organization
  interactionStatistic:
    "@type": InteractionCounter
    interactionType: https://schema.org/DownloadAction
    userInteractionCount: "{package.downloads}"
---
