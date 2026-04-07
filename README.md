# Manage addresses in Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/centrex/laravel-addresses.svg?style=flat-square)](https://packagist.org/packages/centrex/laravel-addresses)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-addresses/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/centrex/laravel-addresses/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-addresses/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/centrex/laravel-addresses/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/centrex/laravel-addresses?style=flat-square)](https://packagist.org/packages/centrex/laravel-addresses)

Polymorphic address management for any Eloquent model. Supports multiple addresses per model with flag-based retrieval (primary, billing, shipping) and country code validation against a seeded countries table.

## Installation

```bash
composer require centrex/laravel-addresses
php artisan vendor:publish --tag="laravel-addresses-migrations"
php artisan migrate
php artisan db:seed --class="Centrex\Addresses\Database\Seeders\CountrySeeder"
```

## Usage

### 1. Add the trait to your model

```php
use Centrex\Addresses\Traits\HasAddresses;

class Customer extends Model
{
    use HasAddresses;
}
```

### 2. Add an address

The `country` field must be a valid ISO 3166-1 alpha-2 code present in the seeded countries table.

```php
$customer->addAddress([
    'country'    => 'BD',
    'city'       => 'Dhaka',
    'state'      => 'Dhaka Division',
    'zip'        => '1000',
    'street'     => '123 Main Street',
    'is_primary' => true,
    'is_billing' => true,
]);
```

### 3. Retrieve addresses

```php
// All addresses
$customer->addresses;

// Check if any addresses exist
$customer->hasAddresses();

// Get by flag (primary | billing | shipping)
$customer->getAddress('billing');    // returns flagged address or falls back
$customer->getAddress('shipping', strict: true);  // only flagged, no fallback

// Latest address (no flag filter)
$customer->getAddress();
```

### 4. Update and delete

```php
$address = $customer->addresses->first();

$customer->updateAddress($address, ['city' => 'Chittagong']);

$customer->deleteAddress($address);

// Remove all
$customer->flushAddresses();
```

### Deprecated helpers (use `getAddress()` instead)

```php
$customer->getPrimaryAddress();
$customer->getBillingAddress();
$customer->getShippingAddress();
```

## Testing

```bash
composer test        # full suite
composer test:unit   # pest only
composer test:types  # phpstan
composer lint        # pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [centrex](https://github.com/centrex)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
