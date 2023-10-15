<?php

namespace Vormkracht10\LaravelPermanentCache\Commands;

use Illuminate\Console\Command;

class LaravelPermanentCacheCommand extends Command
{
    public $signature = 'laravel-permanent-cache';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
