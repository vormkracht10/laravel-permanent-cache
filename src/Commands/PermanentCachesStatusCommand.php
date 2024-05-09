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
    protected $signature = 'permanent-cache:status {--P|parameters}';

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

            $row = [
                $cache->isCached($parameters) ? Emoji::checkMarkButton() : Emoji::crossMarkButton(),
                (new ReflectionClass($cache))->getName(),
                readable_size(strlen($cache->get($parameters))),
                '',
            ];

            if ($this->option('parameters')) {
                $row[] = $this->parseParameters($parameters);
            }

            $cachesTable[] = $row;
            $cachesTable[] = new TableSeparator();
        }

        $this->table(
            [null, 'Cache', 'Size', 'Last Updated'] + ($this->option('parameters') ? ['Parameters'] : []),
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
