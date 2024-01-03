<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Traits;

use Centrex\Addresses\Models\{Address, Contact};
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\{Collection, Model};

/**
 * Class OwnsAddresses
 *
 * @property-read Collection|Address[]  $addresses
 * @property-read Collection|Contact[]  $contacts
 */
trait OwnsAddresses
{
    public function addresses(): HasMany
    {
        /** @var Model $this */
        return $this->hasMany(config('lecturize.addresses.model', Address::class));
    }

    public function contacts(): HasMany
    {
        /** @var Model $this */
        return $this->hasMany(config('lecturize.contacts.model', Contact::class));
    }

    /** @return Address[]|Collection */
    public function getBillingAddresses(): Collection|array
    {
        return $this->addresses()
            ->billing()
            ->get();
    }

    /** @return Address[]|Collection */
    public function getShippingAddresses(): Collection|array
    {
        return $this->addresses()
            ->shipping()
            ->get();
    }
}
