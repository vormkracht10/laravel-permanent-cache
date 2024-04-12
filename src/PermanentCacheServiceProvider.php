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
            ->filter(fn ($parameters, $cacherClass) => is_a($cacherClass, Scheduled::class, true))
            ->map(fn ($parameters, $cacherClass) => $this->app->make($cacherClass, $parameters))
            ->each(fn (Scheduled $cacherClass) => $cacherClass->schedule($schedule->job($cacherClass)))
        );
    }
}
