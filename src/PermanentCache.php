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
        foreach ($cachers as $cacherClass => $parameters) {
            if (is_numeric($cacherClass)) {
                $cacherClass = $parameters;
                $parameters = [];
            }

            $events = $cacherClass::getListenerEvents();

            Event::listen($events, fn () => $this->app->make($cacherClass, $parameters));

            $this->cachers[$cacherClass] = $parameters;
        }

        return $this;
    }

    public function configuredCaches(): array
    {
        return $this->cachers;
    }
}
