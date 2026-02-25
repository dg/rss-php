# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Overview

`dg/rss-php` — a minimal PHP library for consuming RSS and Atom feeds. Single-file library published on Packagist. Requires PHP 8.2+ and `ext-simplexml`.

## Commands

```bash
# Run all tests
composer tester

# Run a single test
vendor/bin/tester tests/Feed.loadRss.phpt -s

# Static analysis (PHPStan level 8)
composer phpstan
```

Coding standards (Nette Code Checker + Nette Coding Standard) run only in CI, not installed locally.

## Architecture

The entire library is a single file `src/Feed.php` containing two classes in the global namespace (classmap autoloading, not PSR-4):

- **`Feed`** — static factory methods `load()`, `loadRss()`, `loadAtom()` that fetch and parse XML, returning a `Feed` instance. Properties are accessed via `__get()` delegating to the underlying `SimpleXMLElement`. Supports file-based caching and HTTP Basic Auth. Namespace-prefixed elements (e.g. `dc:date`, `content:encoded`) are flattened into regular properties.
- **`FeedException`** — marker exception for feed errors.

## Testing

Uses Nette Tester with `.phpt` test files. Tests use fixture XML files from `tests/fixtures/` via a `setupCache()` helper that pre-populates the cache directory, avoiding real HTTP requests.
