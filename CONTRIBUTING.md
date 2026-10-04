# Contributing

Contributions are welcome. Please follow the workflow below before opening a pull request.

## Setup

Fork the repository, then clone your fork:

```bash
git clone https://github.com/YOUR_USERNAME/php-hijri.git
cd php-hijri
composer install
```

Add the original repository as `upstream`:

```bash
git remote add upstream https://github.com/Pharaonic/php-hijri.git
```

## Choose the target branch

Use the branch matching the PHP version you want to support:

```text
8.6.x → PHP 8.6
```

Example:

```bash
git checkout 8.6.x
git pull upstream 8.6.x
```

## Create a working branch

Create a branch from the target version branch:

```bash
git checkout -b fix/invalid-date-conversion
```

Recommended prefixes:

```text
feature/
fix/
refactor/
test/
docs/
```

## Make your changes

- Keep each change focused.
- Add or update tests for behavior changes.
- Preserve backward compatibility whenever possible.
- Update `/docs` when the public API or usage changes.
- Update `CHANGELOG.md` under `[Unreleased]`.
- Do not introduce breaking changes without discussing them in an issue first.

## Run checks

Before opening a pull request:

```bash
composer check
composer validate --strict
```

All checks must pass.

## Push and open a pull request

```bash
git push origin fix/invalid-date-conversion
```

Open the pull request against the same version branch you started from.

Example:

```text
fix/invalid-date-conversion
        ↓
      8.6.x
```

Do not submit the same change to multiple version branches unless requested.

## Security

Do not report security vulnerabilities through public issues.

See [SECURITY.md](SECURITY.md).
