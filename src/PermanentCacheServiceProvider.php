<?php

namespace Vormkracht10\PermamentCache;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\PermanentCache\Commands\UpdatePermanentCacheCommand;

class PermamentCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-permanent-cache')
            ->hasConfigFile()
            ->hasCommand(UpdatePermanentCacheCommand::class);
    }
}
