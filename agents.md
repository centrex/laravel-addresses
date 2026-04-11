# agents.md

## Agent Guidance — laravel-addresses

### Package Purpose
Address management for Eloquent models. Provides an `Address` model, a `HasAddresses` trait, contracts, and helpers for formatting and validation.

### Before Making Changes
- Read `src/Models/` to understand the `Address` model fields
- Read `src/Traits/` to understand the `HasAddresses` trait API
- Read `src/Contracts/` to understand the `Addressable` interface
- Check migrations in `database/migrations/` before touching model fields

### Common Tasks

**Adding a new address field**
1. Create a new migration (never modify existing ones)
2. Add the column to the `$fillable` array in the `Address` model
3. Update helpers in `src/Helpers/` if formatting logic is affected
4. Add a cast if the field needs type coercion (e.g., `json` for structured data)

**Adding a new helper method**
- Add to `src/Helpers/` — keep helpers pure functions where possible
- Register in service provider if the helper needs dependency injection
- Add a test in `tests/`

**Supporting a new country's address format**
- Address formatting varies by country (postal code position, state field, etc.)
- Add format logic to helpers, driven by the `country` field value
- Do not break existing address rendering for other countries

### Testing
```sh
composer test:unit        # pest tests
composer test:types       # phpstan
composer test:lint        # pint
```

### Trait Usage Pattern
The trait attaches a polymorphic `addresses()` relation. When adding new relation methods to the trait, ensure they scope by `addressable_type` and `addressable_id` correctly.

### Safe Operations
- Adding new helper methods
- Adding new model scopes
- Adding new migration columns (append only)
- Adding tests

### Risky Operations — Confirm Before Doing
- Changing the trait's relation method names (breaks callers in host apps)
- Removing or renaming columns in existing migrations
- Changing the polymorphic key names (`addressable_type`, `addressable_id`)

### Do Not
- Hardcode country-specific logic in the `Address` model — keep it in helpers
- Remove the `Contracts/Addressable` interface — it's the extension point
- Skip `declare(strict_types=1)` in any new file
