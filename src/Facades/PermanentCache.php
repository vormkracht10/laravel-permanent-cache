<?php

namespace Vormkracht10\PermanentCache\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\PermanentCache\PermanentCache
 */
class PermanentCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\PermanentCache\PermanentCache::class;
    }
}
