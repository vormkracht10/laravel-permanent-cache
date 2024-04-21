<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use SplObjectStorage;

class PermanentCache
{
    public function __construct(protected SplObjectStorage $cachers, protected Application $app)
    {
    }

    /**
     * @param  array<class-string<Cached|CachedComponent>, int>  $cachers
     */
    public function caches(array $cachers): self
    {
        foreach ($cachers as $cacher => $parameters) {
            if (is_int($cacher)) {
                $cacher = $parameters;
                $parameters = [];
            }

            $cacher = $this->app->make($cacher, $parameters);

            if ([] !== $events = $cacher::getListenerEvents()) {
                Event::listen($events, fn () => $cacher->update($parameters));
            }

            $this->cachers[$cacher] = $events;
        }

        return $this;
    }

    public function configuredCaches(): SplObjectStorage
    {
        return $this->cachers;
    }
}
