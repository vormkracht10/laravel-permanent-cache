<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PermanentCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-permanent-cache');
    }

    public function registeringPackage()
    {
        $this->app->singleton(PermanentCache::class);
    }

    public function bootingPackage()
    {
        $this->callAfterResolving(Schedule::class, fn (Schedule $schedule) => collect(Facades\PermanentCache::staticCaches())
            ->filter(fn ($permanentCache) => is_a($permanentCache, Scheduled::class, true))
            ->map(fn ($permanentCache) => is_array($permanentCache) ? $this->app->make(...$permanentCache) : $this->app->make($permanentCache)))
            ->each(fn (Scheduled $permanentCache) => $permanentCache->schedule($schedule->job($permanentCache)))
        );
    }
}
