<?php

namespace Vormkracht10\PermamentCache;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PermamentCacheServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-permanent-cache')
            ->hasConfigFile()
            ->hasCommand(UpdatePermamentCacheCommand::class);
    }
}
