<?php

namespace Vormkracht10\LaravelPermanentCache\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\LaravelPermanentCache\LaravelPermanentCache
 */
class LaravelPermanentCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\LaravelPermanentCache\LaravelPermanentCache::class;
    }
}
