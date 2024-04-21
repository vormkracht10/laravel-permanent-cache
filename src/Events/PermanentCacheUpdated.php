<?php

namespace Vormkracht10\PermanentCache\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Vormkracht10\PermanentCache\Cached;
use Vormkracht10\PermanentCache\CachedComponent;

class PermanentCacheUpdated
{
    use Dispatchable;

    public function __construct(public readonly Cached|CachedComponent $cache)
    {
        //
    }
}
