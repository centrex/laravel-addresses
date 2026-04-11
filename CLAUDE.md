# CLAUDE.md

## Package Overview

`centrex/laravel-addresses` — Address management for Laravel models, providing models, traits, contracts, and helpers.

Namespace: `Centrex\Addresses\`  
Service Provider: `AddressesServiceProvider`  
Facade: `Facades/Addresses`

## Commands

Run from inside this directory (`cd laravel-addresses`):

```sh
composer install          # install dependencies
composer test             # full suite: rector dry-run, pint check, phpstan, pest
composer test:unit        # pest tests only
composer test:lint        # pint style check (read-only)
composer test:types       # phpstan static analysis
composer test:refacto     # rector refactor check (read-only)
composer lint             # apply pint formatting
composer refacto          # apply rector refactors
composer analyse          # phpstan (alias)
composer build            # prepare testbench workbench
composer start            # build + serve testbench dev server
```

Run a single test:
```sh
vendor/bin/pest tests/ExampleTest.php
vendor/bin/pest --filter "test name"
```

## Structure

```
src/
  Addresses.php
  AddressesServiceProvider.php
  Facades/
  Commands/
  Contracts/
  Exceptions/
  Helpers/
  Http/
  Models/
  Traits/
config/config.php
database/migrations/
tests/
workbench/
```

## Key Concepts

- Models: Address model with standard fields (line1, line2, city, state, country, postal_code)
- Traits: Add to any Eloquent model to give it addressable behavior
- Contracts: Interface for addressable models
- Helpers: Utility functions for address formatting/validation

## Conventions

- PHP 8.2+, `declare(strict_types=1)` in all files
- Pest for tests, snake_case test names
- Pint with `laravel` preset
- Rector targeting PHP 8.3 with `CODE_QUALITY`, `DEAD_CODE`, `EARLY_RETURN`, `TYPE_DECLARATION`, `PRIVATIZATION` sets
- PHPStan at level `max` with Larastan
