<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use ReflectionClass;
use Spatie\Emoji\Emoji;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

class PermanentCachesStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permanent-cache:status {--parameters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show status for all registered Permanent Caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $caches = PermanentCache::configuredCaches();

        foreach ($caches as $value) {
            $cache = $caches->current();
            $parameters = $caches->getInfo();

            $cachesTable[] = [
                $cache->isCached($parameters) ? Emoji::checkMarkButton() : '',
                (new ReflectionClass($cache))->getName(),
                strlen($cache->get($parameters)),
                '',
                $this->option('parameters') ? $this->parseParameters($parameters) : null,
            ];
        }

        $this->table(
            $this->option('parameters') ? ['', 'Cache', 'Parameters', 'Size', 'Last Updated'] : ['', 'Cache', 'Size', 'Last Updated'],
            $cachesTable,
        );
    }

    public function parseParameters($parameters)
    {
        $queryString = http_build_query($parameters);

        return str_replace([
            '=',
            '&',
        ], [
            ': ',
            ', ',
        ], urldecode($queryString));
    }
}
