<?php

namespace Vormkracht10\PermamentCache\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\PermamentCache\PermamentCache
 */
class PermamentCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\PermamentCache\PermamentCache::class;
    }
}
