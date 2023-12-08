<?php

namespace Vormkracht10\PermanentCache\Events;

class PermanentCacheUpdated
{
    public function __construct(public $class)
    {
    }
}
