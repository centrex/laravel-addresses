<?php

declare(strict_types = 1);

use Centrex\Addresses\Models\Country;
use Centrex\Addresses\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;

it('creates and retrieves addresses using the normalized package config', function (): void {
    Country::query()->forceCreate([
        'name' => 'United States',
        'iso_3166_2' => 'US',
        'iso_3166_3' => 'USA',
    ]);

    $customer = TestCustomer::query()->create(['name' => 'Acme']);

    $address = $customer->addAddress([
        'country' => 'US',
        'street' => '123 Main Street',
        'city' => 'Austin',
        'post_code' => '73301',
        'is_primary' => true,
    ]);

    expect($customer->hasAddresses())->toBeTrue()
        ->and($address->country_id)->not->toBeNull()
        ->and($customer->getAddress('primary')?->id)->toBe($address->id);
});

class TestCustomer extends Model
{
    use HasAddresses;

    protected $table = 'customers';

    protected $guarded = [];
}
