<?php

namespace Centrex\Addresses\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface AddressableInterface
 * @package Centrex\Addresses\Contracts
 */
interface AddressableInterface
{
    public function addresses(): MorphMany;
}