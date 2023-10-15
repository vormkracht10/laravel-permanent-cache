<?php

namespace Vormkracht10\PermamentCache\Events;

class PermanentCacheUpdateFailed
{
    public function __construct(public $class)
    {
    }
}
