<?php

namespace Vormkracht10\PermanentCache\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Concerns\InteractsWithIO;
use Vormkracht10\PermamentCache\Events\PermanentCacheUpdated;
use Vormkracht10\PermamentCache\Events\PermanentCacheUpdateFailed;
use Vormkracht10\PermamentCache\Facades\PermanentCache;
use Vormkracht10\PermanentCache\Exceptions\CouldNotUpdatePermanentCache;

class UpdatePermanentCacheCommand extends Command
{
    use InteractsWithIO;

    public $signature = 'permanent-cache:update';

    public $description = 'Updates permanent cache';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }

    public function updateCaches()
    {
        return app(PermanentCache::class)
            ->configuredCaches()
            ->map(fn ($class) => is_string($class) ? app($class) : $class)
            ->map(function ($class): Result {
                return $class->shouldUpdateCache()
                    ? $this->updateCache($check)
                    : $this->components->info("Skipped updating cache for {$class->getName()}");
            });
        }
    }

    public function updateCache($class)
    {
        try {
            $this->output->write("<comment>Updating cache: {$class->getName()}...</comment> ", false);

            $result = $class->updateCache();

            event(new PermanentCacheUpdated($class));
        } catch (Exception $exception) {
            $exception = CouldNotUpdatePermanentCache::make($check, $exception);

            report($exception);

            $this->thrownExceptions[] = $exception;

            event(new PermanentCacheUpdateFailed($check, $result));
        }
    }
}
