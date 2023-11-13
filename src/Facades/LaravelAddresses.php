<?php

declare(strict_types=1);

namespace Centrex\LaravelAddresses\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\LaravelAddresses\LaravelAddresses
 */
class LaravelAddresses extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\LaravelAddresses\LaravelAddresses::class;
    }
}
