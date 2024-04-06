<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\Facades\Event;

class PermanentCache
{
    /**
     * @var array<class-string, array<class-string<Cached>, class-string<CachedComponent>>>
     */
    protected array $cachers = [];

    /**
     * @param  array<int, class-string<Cached>, class-string<CachedComponent>>  $cachers
     * @return $this
     */
    public function caches(array $cachers): self
    {
        /** @var <class-string<Cached>, class-string<CachedComponent>> $cacher */
        foreach ($cachers as $cacher) {
            $events = $cacher::getListenerEvents();

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

    public function staticCaches(): array
    {
        return array_keys(array_filter($this->cachers, fn ($events) => empty($events)));
    }
}
