<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use ReflectionClass;
use Spatie\Emoji\Emoji;
use SplObjectStorage;
use Symfony\Component\Console\Helper\ProgressBar;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

class UpdatePermanentCachesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permanent-cache:update {--filter=}';

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
        $configuredCaches = PermanentCache::configuredCaches();
        $caches = new SplObjectStorage;

        foreach ($configuredCaches as $c) {
            $cache = $configuredCaches->current();
            $parameters = $configuredCaches->getInfo();

            if (
                $this->option('filter') &&
                !str_contains(strtolower($cache->getName()), strtolower($this->option('filter')))
            ) {
                continue;
            }

            $caches[$cache] = $parameters;
        }

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $progressBar = $this->output->createProgressBar($caches->count());

        $progressBar->setFormat('custom');

        $progressBar->setMessage('Starting...');

        $progressBar->start();

        foreach ($caches as $c) {
            $cache = $caches->current();
            $parameters = $caches->getInfo();

            $cache->update($parameters);

            $currentTask = $cache->getName();
            $emoji = ($progressBar->getProgress() % 2 ? Emoji::hourglassNotDone() : Emoji::hourglassDone());

            $progressBar->setMessage('Updating: '.$currentTask.' '.$emoji);
            $progressBar->advance();
        }

        $progressBar->setMessage('Finished!');
        $progressBar->finish();
    }
}
