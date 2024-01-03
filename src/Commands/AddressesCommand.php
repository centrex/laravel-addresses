<?php

declare(strict_types = 1);

namespace Centrex\Addresses\Commands;

use Illuminate\Console\Command;

class AddressesCommand extends Command
{
    public $signature = 'addresses';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
