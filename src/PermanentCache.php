<?php

namespace Vormkracht10\PermamentCache;

class PermanentCache
{
    protected array $caches = [];

    public function caches(array $caches): self
    {
        $this->caches = array_merge($this->caches, $caches);

        return $this;
    }

    public function configuredCaches()
    {
        return collect($this->caches);
    }
}
