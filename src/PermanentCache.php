<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\Facades\Event;

class PermanentCache
{
    /**
     * @var array<class-string, array<class-string<Cached>>>
     */
    protected array $cachers = [];

    /**
     * @param  array<int, class-string<Cached>>  $cachers
     * @return $this
     */
    public function caches(array $cachers): self
    {
        foreach ($cachers as $cacher) {
            $events = $cacher::getListenerEvent();

            $resolved[$cacher] = $events;

            Event::listen($events, $cacher);

        }

        $this->cachers = array_merge($this->cachers, $resolved ?? []);

        return $this;
    }

    public function configuredCaches(): array
    {
        return $this->cachers;
    }
}
