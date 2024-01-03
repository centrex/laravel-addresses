<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface AddressableInterface
 */
interface AddressableInterface
{
    public function addresses(): MorphMany;
}
