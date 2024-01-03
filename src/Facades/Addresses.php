<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Addresses\Addresses
 */
class Addresses extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\Addresses\Addresses::class;
    }
}
