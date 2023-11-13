<?php

declare(strict_types=1);

namespace Centrex\LaravelAddresses\Commands;

use Illuminate\Console\Command;

class LaravelAddressesCommand extends Command
{
    public $signature = 'laravel-addresses';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
