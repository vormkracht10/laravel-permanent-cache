<?php

namespace Vormkracht10\PermanentCache\Events;

class PermanentCacheUpdateFailed
{
    public function __construct(public $class)
    {
    }
}
