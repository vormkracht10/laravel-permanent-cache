<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\PermanentCache\Commands\PermanentCachesStatusCommand;
use Vormkracht10\PermanentCache\Commands\UpdatePermanentCachesCommand;

class PermanentCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-permanent-cache')
            ->hasCommands(
                PermanentCachesStatusCommand::class,
                UpdatePermanentCachesCommand::class
            )
            ->hasRoute('api')
            ->hasConfigFile();
    }

    public function registeringPackage()
    {
        $this->app->singleton(PermanentCache::class);
    }

    public function bootingPackage()
    {
        $this->callAfterResolving(
            Schedule::class,
            fn (Schedule $schedule) => collect(Facades\PermanentCache::configuredCaches())
                ->filter(fn ($cacher) => is_a($cacher, Scheduled::class))
                ->each(fn ($cacher) => $cacher->schedule($schedule->job($cacher)))
        );
    }
}
