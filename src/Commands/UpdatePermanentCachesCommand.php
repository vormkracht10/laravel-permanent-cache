<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

class UpdatePermanentCachesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permanent-cache:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all registered Permanent Caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PermanentCache::update();
    }
}
