<?php

namespace Vormkracht10\PermanentCache\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vormkracht10\PermanentCache\PermanentCacheServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PermanentCacheServiceProvider::class,
        ];
    }
}
