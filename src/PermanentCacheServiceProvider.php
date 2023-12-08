<?php

namespace Vormkracht10\PermanentCache;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\PermanentCache\Commands\UpdatePermanentCacheCommand;

class PermanentCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-permanent-cache')
            ->hasConfigFile()
            ->hasCommand(UpdatePermanentCacheCommand::class);
    }
}
