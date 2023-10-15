<?php

namespace Vormkracht10\LaravelPermanentCache;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\LaravelPermanentCache\Commands\LaravelPermanentCacheCommand;

class LaravelPermanentCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-permanent-cache')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-permanent-cache_table')
            ->hasCommand(LaravelPermanentCacheCommand::class);
    }
}
