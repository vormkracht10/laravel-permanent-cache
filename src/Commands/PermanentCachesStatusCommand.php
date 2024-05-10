<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Lorisleiva\CronTranslator\CronTranslator;
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

        $frequencies = collect(app(Schedule::class)->events())
            ->mapWithKeys(function ($schedule) {
                return [$schedule->description => CronTranslator::translate($schedule->expression)];
            });

        $tableRows = [];

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
                $cached ? readable_size(strlen(serialize($cached))) : 'N/A',
                $cached?->updated_at?->diffForHumans() ?: 'N/A',
                $frequencies[$cache->getName()] ?? 'N/A',
            ];

            if ($this->option('parameters')) {
                $row[] = $this->parseParameters($parameters);
            }

            $tableRows[] = $row;
            $tableRows[] = new TableSeparator();
        }

        array_pop($tableRows);

        $this->table(
            [null, 'Cache', 'Size', 'Last Updated', 'Frequency'] + ($this->option('parameters') ? ['Parameters'] : []),
            $tableRows,
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
