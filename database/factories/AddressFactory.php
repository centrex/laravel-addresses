<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Database\Factories;

use Centrex\Addresses\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'street'     => $this->faker->streetAddress(),
            'city'       => $this->faker->city(),
            'state'      => $this->faker->state(),
            'post_code'  => $this->faker->postcode(),
            'country_id' => null,
            'is_primary' => false,
            'is_billing' => false,
            'is_shipping' => false,
        ];
    }
}
