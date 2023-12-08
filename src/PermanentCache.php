<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Vormkracht10\PermanentCache\Events\UpdatingPermanentCacheEvent;

class PermanentCache
{
    protected array $cachers = [];

    /**
     * @param array<int, class-string<Cached>> $cachers
     *
     * @return $this
     */
    public function caches(array $cachers): self
    {
        foreach ($cachers as $cacher) {
            $event = $cacher::getListenerEvent();

            $resolved[$event][] = $cacher;

            Event::listen($event, $cacher);
        }

        $this->cachers = array_merge($this->cachers, $resolved ?? []);

        return $this;
    }

    /**
     * @return Collection<class-string, array<class-string>>
     */
    public function configuredCaches(): Collection
    {
        return collect($this->cachers);
    }
}
