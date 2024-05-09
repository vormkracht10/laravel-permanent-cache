<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use ReflectionClass;
use Spatie\Emoji\Emoji;
use Symfony\Component\Console\Helper\TableSeparator;
use Vormkracht10\PermanentCache\Facades\PermanentCache;

class PermanentCachesStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permanent-cache:status {--P|parameters} {--F|filter=}';

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

        foreach ($caches as $c) {
            $cache = $caches->current();
            $parameters = $caches->getInfo();

            if (
                $this->option('filter') &&
                ! str_contains(strtolower($cache->getName()), strtolower($this->option('filter')))
            ) {
                continue;
            }

            $cached = $cache->getMeta($parameters);

            $row = [
                $cache->isCached($parameters) ? Emoji::checkMarkButton() : Emoji::crossMark(),
                $cache->getName(),
                is_object($cached) ? readable_size($cached->size) : 'N/A',
                is_object($cached) ? $cached->expression : 'N/A',
                is_object($cached) ? $cached->updated_at->diffForHumans() : 'N/A',
            ];

            if ($this->option('parameters')) {
                $row[] = $this->parseParameters($parameters);
            }

            $cachesTable[] = $row;
            $cachesTable[] = new TableSeparator();
        }

        array_pop($cachesTable);

        $this->table(
            [null, 'Cache', 'Size', 'Frequency', 'Last Updated'] + ($this->option('parameters') ? ['Parameters'] : []),
            $cachesTable,
            'box',
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
