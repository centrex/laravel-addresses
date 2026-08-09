# Known Issues — laravel-addresses

_Last checked: 2026-08-02_

## Failing tests

No failing tests. `vendor/bin/pest -p` reports **2 passed** (6 assertions) — but the suite is very thin: `tests/` only contains `ExampleTest.php` and `ArchTest.php` (plus `Pest.php`/`TestCase.php` bootstrap). There is no dedicated test coverage for `Address`, `Contact`, `Country`, `HasCountry`, or `NameGenerator`, despite these being the package's main models/helpers.

## Style / static-analysis debt

- `vendor/bin/pint --test` reports **5 files** with unapplied fixers: `src/Models/Address.php` (`new_with_parentheses`, `new_with_braces`, `binary_operator_spaces`), `src/Helpers/NameGenerator.php` (`braces`, `no_superfluous_phpdoc_tags`, `single_line_empty_body`, `phpdoc_align`), `database/migrations/2023_11_15_010000_create_addresses_table.php`, `database/migrations/2023_11_15_020000_create_contacts_table.php`, and `database/factories/AddressFactory.php`. Run `composer lint` to apply.
- `vendor/bin/rector --dry-run` reports **9 files** with pending refactors, mostly `AddOverrideAttributeToOverriddenMethodsRector` (missing `#[\Override]` on `boot()`/`getFacadeAccessor()`/`toArray()` overrides across Facades, Models, and Http/Resources) plus one `ReturnBinaryOrToEarlyReturnRector` in `src/Models/Address.php` and one `RemoveUselessParamTagRector` in `src/Helpers/NameGenerator.php`. Run `composer refacto` to apply.
- PHPStan (`level: max`) reports **107 errors**. `phpstan-baseline.neon` exists but is **empty (0 lines)**, so all 107 errors are live/unbaselined. Most are generic-type specification gaps (`BelongsTo`/`Builder` return types missing `TRelatedModel`/`TModel` template args) and `where()`/`orWhere()` calls passed non-Eloquent-property column names (e.g. `iso_3166_2`, `iso_3166_3` in `src/Traits/HasCountry.php`) that PHPStan can't resolve as real model properties.

## TODO / FIXME markers

None found (`grep -rn "TODO\|FIXME" --include="*.php" src/ config/ database/` — no matches).

## Open GitHub issues

Not checked — the `gh` CLI is not installed in this environment.
