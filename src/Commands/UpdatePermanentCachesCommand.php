<?php

namespace Vormkracht10\PermanentCache\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\Emoji\Emoji;
use SplObjectStorage;
use Symfony\Component\Console\Helper\ProgressBar;
use Vormkracht10\PermanentCache\CachesValue;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

class UpdatePermanentCachesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permanent-cache:update {--filter=} {--connection=} {--queue=}';

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
                ! str_contains(strtolower($cache->getName()), strtolower($this->option('filter')))
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
            /** @var CachesValue $cache */
            $cache = $caches->current();

            $currentTask = $cache->getName();
            $emoji = ($progressBar->getProgress() % 2 ? Emoji::hourglassNotDone() : Emoji::hourglassDone());

            $progressBar->setMessage('Updating: '.$currentTask.' '.$emoji);

            $connection = $this->option('connection') ?? 'sync';

            $queue = $connection === 'sync' ? null : $this->option('queue');

            try {
                dispatch($cache)
                    ->onConnection($connection)
                    ->onQueue($queue);
            } catch (Exception) {
                $progressBar->setMessage('Error: '.$currentTask.' '.Emoji::warning());

                sleep(2);
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Finished!');
        $progressBar->finish();
    }
}
