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
        $this->callAfterResolving(Schedule::class, fn (Schedule $schedule) => collect(Facades\PermanentCache::configuredCaches())
            ->filter(fn ($cacher) => is_a($cacher, Scheduled::class))
            ->each(fn (Scheduled $instance) => $instance->schedule($schedule->job($instance)))
        );
    }
}
