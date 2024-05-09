<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use ReflectionClass;
use Symfony\Component\Console\Helper\ProgressBar;
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
        $caches = collect(
            PermanentCache::configuredCaches()
        );

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $progressBar = $this->output->createProgressBar($caches->count());

        $progressBar->setFormat('custom');

        $progressBar->setMessage('Starting...');

        $progressBar->start();

        $caches->each(function ($cache) use ($progressBar) {
            $cache->update();

            $progressBar->setMessage('Updating: '.(new ReflectionClass($cache))->getName());
            $progressBar->advance();
        });

        $progressBar->setMessage('Finished!');
        $progressBar->finish();
    }
}
