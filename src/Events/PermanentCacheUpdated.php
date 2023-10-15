<?php

namespace Vormkracht10\PermamentCache\Events;

class PermanentCacheUpdated
{
    public function __construct(public $class)
    {
    }
}
