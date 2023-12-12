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
        $this->callAfterResolving(Schedule::class, fn (Schedule $schedule) =>
            collect(\Vormkracht10\PermanentCache\Facades\PermanentCache::staticCaches())
                ->filter(fn ($c) => is_a($c, Scheduled::class, true))
                ->map(fn ($c) => $this->app->make($c))
                ->each(fn (Scheduled $c) => $c->schedule($schedule->job($c)))
        );
    }
}
